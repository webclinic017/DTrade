<?php

namespace App;

use App\Events\Portfolio\NotEnoughCashAvailable;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $appends = ['value', 'title'];
    protected $fillable = ['cash', 'user_id', 'platform_data_id'];

    public function stocks()
    {
        return $this->belongsToMany(Stock::class)->withPivot(['shares']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platform()
    {
        return $this->belongsTo(PlatformData::class, 'platform_data_id', 'id');
    }

    private function maximumQuantityAffordable(Stock $stock)
    {
        return floor($this->cash / $stock->value);
    }

    private function handleCashChange(Stock $stock, int $change)
    {
        $maximumQuantity = $this->maximumQuantityAffordable($stock);
        if ($change > 0 && $maximumQuantity < $change) {
            $message = "You did not have enough cash in your portfolio to buy all $change shares of $stock->symbol. $maximumQuantity were purchased instead.";
            event(new NotEnoughCashAvailable($this->user, $message));

            return $maximumQuantity;
        }

        $this->cash -= $stock->value * $change;
        $this->save();

        return $change;
    }

    public function modifyPortfolio(Stock $stock, int $change)
    {
        $change = $this->handleCashChange($stock, $change);
        // prepare a query of all stocks in a portfolio, see if the given stock
        // exists in that list
        $stocksQuery = $this->stocks();
        $stockPivot = $stocksQuery->where('stock_id', $stock->id);
        if ($stockPivot->exists()) {
            // the stock is already in the users portfolio, change the number of
            // shares by the given amount. Remove the stock if they sold all
            // of the shares they have.
            $stockPivot = $stockPivot->first();
            $newShares = $stockPivot->pivot->shares + $change;
            if ($newShares > 0) {
                $stocksQuery->updateExistingPivot($stockPivot->id, ['shares' => $newShares]);
            } else {
                $stocksQuery->detach($stock->id);
            }
        } elseif ($change > 0) {
            // the stock was not in the users portfolio and they bought shares..
            // add it to their portfolio with the given amount of shares.
            $stocksQuery->attach($stock->id, ['shares' => $change]);
        }

        return $change;
    }

    private function computeExpectedMovement(Stock $stock)
    {
        $projections = [$stock->nextDay, $stock->fiveDay, $stock->tenDay];

        return collect($projections)->map(function ($projection) {
            $projectedMovement = 0;

            switch (str_replace([' profit', ' loss'], '', $projection['verdict'])) {
                case 'small':
                    $projectedMovement = .02;
                    break;
                case 'moderate':
                    $projectedMovement = .05;
                    break;
                case 'large':
                    $projectedMovement = .10;
                    break;
            }

            if (strpos($projection['verdict'], 'loss')) {
                $projectedMovement = -$projectedMovement;
            }

            return 1 + ($projectedMovement * ($projection['accuracy'] / 100));
        })->average();
    }

    private function recommendedPositionFor(Stock $stock)
    {
        return $stock->recommendedPositionFor($this->user);
    }

    private function costOfOptimalShares(Stock $stock)
    {
        return $this->recommendedPositionFor($stock) * $stock->value;
    }

    public function getValueAttribute()
    {
        $value = $this->cash;
        $this->stocks->each(function (Stock $stock) use (&$value) {
            $value += $stock->pivot->shares * $stock->value;
        });

        return round($value, 2);
    }

    public function getTitleAttribute()
    {
        return $this->platform->platform;
    }

    public function getRecommendedStocksAttribute()
    {
        $totalCost = 0;
        // Do not worry about stocks we have currently, only how to best utilize
        // the cash we have in our account.
        return Stock::all()->filter(function (Stock $stock) {
            $optimalPositionSize = $this->recommendedPositionFor($stock);
            $canAffordOptimalShares = $this->costOfOptimalShares($stock) < $this->cash;

            // Dont want to recommend stocks that are already in our portfolio, in bad shape, or that we cant afford.
            return !$this->stocks->contains($stock) && $optimalPositionSize > 0 && $canAffordOptimalShares;
        })->sortBy(function (Stock $stock) {
            // Sort the stocks by their average expected return
            return $this->computeExpectedMovement($stock);
        })->filter(function (Stock $stock) use (&$totalCost) {
            // Keep as many as we can afford to buy together
            $costOfOptimalShares = $this->costOfOptimalShares($stock);
            if ($totalCost + $costOfOptimalShares < $this->cash) {
                $totalCost += $costOfOptimalShares;

                return true;
            }

            return false;
        })->map(function (Stock $stock) {
            // Display useful information
            return [
                'symbol' => $stock->symbol,
                'shares' => $this->recommendedPositionFor($stock),
                'cost'   => $this->costOfOptimalShares($stock),
            ];
        });
    }
}
