<?php

namespace App\Jobs\Stocks;

use App\Automation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunAutomations implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 400;

    public function handle()
    {
        Automation::all()->each(function (Automation $automation) {
            $automation->execute();
        });
    }
}
