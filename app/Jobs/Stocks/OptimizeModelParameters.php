<?php

namespace App\Jobs\Stocks;

use App\ModelParameter;
use App\Stock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeModelParameters extends StockJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    /**
     * Execute the job.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function handle()
    {
        $stock = Stock::fetch($this->symbol);
        if ($stock instanceof Stock) {
            if ($stock->modelParameters instanceof ModelParameter) {
                $stock->modelParameters->optimize();
            }
        }
    }
}
