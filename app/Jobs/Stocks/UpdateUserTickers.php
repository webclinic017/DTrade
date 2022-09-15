<?php

namespace App\Jobs\Stocks;

use App\AlphaVantageApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUserTickers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        AlphaVantageApi::all()->each(function (AlphaVantageApi $api) {
            $api->updateMostOutdatedTicker();
        });
    }
}
