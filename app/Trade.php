<?php

namespace App;

use App\Jobs\Robinhood\StockOrderJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Trade extends Model
{
    protected $fillable = ['order', 'order_type', 'shares', 'order_details', 'executed', 'user_id', 'stock_id', 'automation_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function automation()
    {
        return $this->belongsTo(Automation::class);
    }

    public function scopeThisWeek(Builder $query)
    {
        return $query->where('created_at', '>', Carbon::today()->subDays(7));
    }

    public function isDayTrade()
    {
        if ($this->order_type === 'sell' && $this->executed) {
            $startOfTradingTime = Carbon::today()->addHours(9)->addMinutes(30);

            return $this->user->trades->where([
                ['stock_id', '=', $this->stock->id],
                ['order_type', '=', 'buy'],
                ['updated_at', '>', $startOfTradingTime],
            ])->exists();
        }

        return false;
    }

    public function craftOrder()
    {
        $orderDetails = [
            'order'         => $this->order,
            'order_type'    => $this->order_type,
            'ticker'        => $this->stock->symbol,
            'shares'        => $this->shares,
        ];

        if ($this->order_details) {
            $additionalData = json_decode($this->order_details, true);
            $orderDetails = array_merge($additionalData, $orderDetails);
        }

        return $orderDetails;
    }

    public function execute()
    {
        if ($this->isDayTrade()) {
            if ($this->user->numberDayTrades > 2) {
                return false;
            }
        }

        StockOrderJob::dispatch($this->craftOrder(), $this->user, ['order', $this->stock->symbol]);

        $this->executed = true;
        $this->save();

        return true;
    }
}
