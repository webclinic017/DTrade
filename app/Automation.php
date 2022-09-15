<?php

namespace App;

use App\Events\Portfolio\StockOrderSent;
use App\Events\Portfolio\TooManyDayTrades;
use App\Traits\Math;
use Illuminate\Database\Eloquent\Model;

class Automation extends Model
{
    protected $appends = ['needsBalancing', 'balancingQuantity', 'shouldTrade', 'orderSize', 'orderType'];
    protected $fillable = ['user_id', 'stock_id', 'enabled'];

    use Math;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function disable()
    {
        $this->enabled = false;
        $this->save();

        return $this;
    }

    public function enable()
    {
        $this->enabled = true;
        $this->save();

        return $this;
    }

    private function dataLimit($dataSource, $periods)
    {
        return array_slice($dataSource, -$periods, $periods);
    }

    public function getPriceSlope($periods)
    {
        $lastUpdate = $this->stock->lastUpdate->close;
        $closings = $this->stock->close;
        array_push($closings, $lastUpdate);
        $close = $this->dataLimit($closings, $periods);

        return $this->computeSlope($close);
    }

    private function getRsiSlope($periods)
    {
        $rsi = $this->dataLimit($this->stock->rsi, $periods);

        return $this->computeSlope($rsi);
    }

    private function getCurrentRsi()
    {
        return $this->dataLimit($this->stock->rsi, 1)[0];
    }

    private function isOptimalBuyTime()
    {
        if ($this->getCurrentRsi() < 40) {
            $rsiSlope = $this->getRsiSlope(2);
            $priceSlope = $this->getPriceSlope(2);

            // if the rsi and price is increasing, buy
            return $rsiSlope > 0 && $priceSlope > 0;
        }

        return false;
    }

    private function isOptimalSellTime()
    {
        if ($this->getCurrentRsi() > 80) {
            $rsiSlope = $this->getRsiSlope(2);
            $priceSlope = $this->getPriceSlope(2);

            // If the rsi or price is rising slowly or falling, sell
            return $rsiSlope < 1 || $priceSlope < 1;
        }

        return false;
    }

    private function isLocallyOptimalBuyTime()
    {
        if ($this->getCurrentRsi() < 50) {
            $priceSlope = $this->getPriceSlope(4);

            // Rsi is low and price is rising
            return $priceSlope > 0;
        }

        return false;
    }

    private function isLocallyOptimalSellTime()
    {
        if ($this->getCurrentRsi() > 50) {
            $priceSlope = $this->getPriceSlope(4);

            // Rsi is high and price is rising slowly or falling
            return $priceSlope < 1;
        }

        return false;
    }

    private function currentVsRecommendedDiff()
    {
        return $this->stock->recommendedPositionFor($this->user) - $this->stock->currentPositionFor($this->user);
    }

    private function getOrderSizeScalar()
    {
        switch ($this->orderType) {
            case 'buy':
                return $this->isOptimalBuyTime() ? 1 : $this->isLocallyOptimalBuyTime() ? 0.5 : 0;
                break;
            case 'sell':
                return $this->isOptimalSellTime() ? 1 : $this->isLocallyOptimalSellTime() ? 0.5 : 0;
                break;
            case 'hold':
                return 0;
                break;
        }

        return 0;
    }

    public function execute()
    {
        if ($this->enabled && $this->shouldTrade) {
            // Make sure we can buy the stocks with the balance we have, also do
            // the portfolio edits necessary to keep everything as synced as possible.
            $orderSize = $this->user->portfolio->modifyPortfolio($this->stock, $this->orderSize);

            $trade = $this->trades()->firstOrCreate([
                'order'         => $this->orderType,
                'shares'        => $orderSize,
                'user_id'       => $this->user->id,
                'stock_id'      => $this->stock->id,
                'order_type'    => 'market',
                'executed'      => false,
            ]);

            $stock = $this->stock->symbol;
            $order = $this->orderType === 'buy' ? 'purchase' : $this->orderType;

            if ($trade->execute()) {
                event(new StockOrderSent($this->user, "Your $order order of $stock has been sent."));
            } else {
                event(new TooManyDayTrades($this->user, "Your $order order of $stock failed because too many day trades have occurred in the past week."));
            }
        }
    }

    public function getNeedsBalancingAttribute()
    {
        return $this->enabled ? $this->currentVsRecommendedDiff() !== 0.0 : false;
    }

    public function getBalancingQuantityAttribute()
    {
        return $this->currentVsRecommendedDiff();
    }

    public function getShouldTradeAttribute()
    {
        return $this->getOrderSizeScalar() > 0;
    }

    public function getOrderSizeAttribute()
    {
        return floor($this->getOrderSizeScalar() * abs($this->currentVsRecommendedDiff()));
    }

    public function getOrderTypeAttribute()
    {
        $diff = $this->currentVsRecommendedDiff();
        if ($diff === 0) {
            return 'hold';
        }

        return $diff > 0 ? 'buy' : 'sell';
    }
}
