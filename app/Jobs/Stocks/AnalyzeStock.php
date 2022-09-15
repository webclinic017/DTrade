<?php

namespace App\Jobs\Stocks;

use App\Stock;
use App\StockProjection;

class AnalyzeStock extends StockJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $stock = Stock::fetch($this->symbol);
        StockProjection::makeFor($stock);
    }
}
