<?php

namespace App\Console;

use App\Jobs\Stocks\AnalyzeStock;
use App\Jobs\Stocks\CheckAccuracy;
use App\Jobs\Stocks\MarkEndOfDayData;
use App\Jobs\Stocks\OptimizeModelParameters;
use App\Jobs\Stocks\RunAutomations;
use App\Jobs\Stocks\UpdateSharedTickers;
use App\Jobs\Stocks\UpdateUserTickers;
use App\Stock;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    public static $userStockUpdatesPerMinute = 4;
    public static $sharedStockUpdatesPerMinute = 200;

    public static function analyzeStocks()
    {
        $symbols = Stock::all()->pluck('symbol');
        $symbols->each(function ($symbol) {
            AnalyzeStock::withChain([
                new CheckAccuracy($symbol),
            ])->dispatch($symbol);
        });
        $symbolsString = '`'.implode('`, ', $symbols->toArray()).'``.';
        Log::debug("Launched chained jobs to analyze, and check prediction accuracy for $symbolsString");
    }

    /**
     * Update the most outdated ticker $updatesPerMinute times a minute.
     *
     * By default this is set to 4, one less than the Alpha Vantage Free API
     * limit. This may change if we need more manual requests in the future, or
     * if we upgrade our API to one of their paid services.
     *
     * If we do end up upgrading, we should add the ability for this to be
     * configurable on a per-api basis, not globally as it is right now. This
     * would also allow our users to set the number of times a minute they would
     * like us to update their stocks. A potential way to do this would be to
     * run the update job 30 times a minute, and have a check in each data source
     * to see if it is being run too frequently for its API tier.
     */
    public static function keepTickersUpdated(Carbon &$now = null)
    {
        $now = ($now === null) ? Carbon::now() : $now;
        $updateInterval = 1000000 * (60 / self::$sharedStockUpdatesPerMinute);
        $sharedUpdatesPerUserUpdate = self::$sharedStockUpdatesPerMinute / self::$userStockUpdatesPerMinute;

        for ($i = 0; $i < self::$sharedStockUpdatesPerMinute; $i++) {
            UpdateSharedTickers::dispatch();
            if ($i % $sharedUpdatesPerUserUpdate === 0) {
                UpdateUserTickers::dispatch();
            }
            usleep($updateInterval);
        }
    }

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Keep stock data updated during trading hours.
        $schedule->call(function () {
            self::keepTickersUpdated();
        })->everyMinute()->weekdays()->between('09:30', '16:00');

        // Ensure automations are run throughout the day
        $schedule->call(function () {
            RunAutomations::dispatch();
        })->hourly()->weekdays()->between('09:30', '16:00');

        // At the end of the trading day mark the last intraday update as the
        // EOD data point. Dispatch a minute after market close to allow pending
        // updates to finish.
        $schedule->call(function () {
            $stocks = Stock::all()->pluck('symbol')->toArray();
            MarkEndOfDayData::dispatch($stocks);
        })->dailyAt('16:01');

        // After the last data point for each stock has been designated the EOD
        // quote, reanalyze projections for the next day.
        $schedule->call(function () {
            self::analyzeStocks();
        })->dailyAt('16:02');

        // Snapshot horizon logs for metric tracking every hour
        $schedule->call(function () {
            Artisan::call('horizon:snapshot');
            Log::debug('horizon:snapshot');
        })->hourly();

        $schedule->call(function () {
            $stocks = Stock::all()->pluck('symbol')->toArray();
            OptimizeModelParameters::dispatch($stocks);
        })->twiceDaily(7, 17);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
