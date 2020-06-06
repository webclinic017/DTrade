<?php

namespace App\Jobs\Stocks;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class StockJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 400;

    protected $symbol;

    /**
     * Create a new job instance.
     *
     * @param string|array $symbols - the ticker symbol(s) in question.
     */
    public function __construct($symbols)
    {
        if (is_array($symbols)) {
            $this->symbol = array_pop($symbols);
            if (count($symbols)) {
                $jobClass = get_class($this);
                $jobClass::dispatch($symbols);
            }
        } else {
            $this->symbol = $symbols;
        }
    }
}
