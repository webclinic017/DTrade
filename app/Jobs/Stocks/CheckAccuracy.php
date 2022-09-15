<?php

namespace App\Jobs\Stocks;

use App\ModelAccuracy;
use App\Stock;

class CheckAccuracy extends StockJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $stock = Stock::fetch($this->symbol);
        ModelAccuracy::test($stock);
    }
}
