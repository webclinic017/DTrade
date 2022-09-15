<?php

namespace App\Console\Commands;

use App\Jobs\Stocks\OptimizeModelParameters as OptimizeModelParametersJob;
use Illuminate\Console\Command;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeModelParameters extends Command
{
    use SerializesModels;
    use InteractsWithQueue;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:optimize {symbol}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Launch a job to optimize the model parameters for a given stock.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $symbol = $this->argument('symbol');
        OptimizeModelParametersJob::dispatch($symbol);
        $this->info("Launched a job to optimize $symbol's SVM.");
    }
}
