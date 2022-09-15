<?php

namespace App\Traits;

use App\Jobs\Stocks\UpdateTickerData;
use App\Ticker;
use Carbon\Carbon;

trait DataSource
{
    public $lastTradingDayQuoteKey;

    protected $maxAutomaticRequestsPerDay;

    abstract public function tickers();

    abstract public function quote(string $symbol);

    abstract public function dailyHistory(string $symbol);

    /**
     * Method that finds the ticker that hasn't been updated in the most amount
     * of time.
     *
     * @return Ticker
     */
    public function getMostOutdatedTicker()
    {
        return $this->tickers()
            ->orderBy('updated_at', 'ASC')
            ->limit(1)
            ->first();
    }

    /**
     * Method that computes the amount of time between two updates for a ticker
     * this api is responsible for. This number is in seconds. Currently, updates
     * are limited to a maximum of 1 per second per ticker.
     *
     * @return int
     */
    public function computeUpdateInterval()
    {
        $numberOfTickers = $this->tickers()->count();
        // 23,400 the number of seconds in a trading day (6.5 hours). This
        // formula is designed to update tickers at the fastest possible rate
        // without hitting the API limit before the end of the day.
        $computedInterval = (23400 * $numberOfTickers) / $this->maxAutomaticRequestsPerDay;

        return max(round($computedInterval), 1);
    }

    /**
     * Updates the most outdated Ticker as long as it has not been updated within
     * the updateInterval computed above. The amount of time between updates
     * changes as the api is responsible for more tickers.
     */
    public function updateMostOutdatedTicker()
    {
        $now = Carbon::now();
        $updateInterval = $this->computeUpdateInterval();
        $mostOutdatedTicker = $this->getMostOutdatedTicker();
        if ($mostOutdatedTicker) {
            $lastUpdate = $mostOutdatedTicker->updated_at;
            if ($now->diffInSeconds($lastUpdate) > $updateInterval) {
                UpdateTickerData::dispatch($mostOutdatedTicker->symbol);
            }
        }
    }
}
