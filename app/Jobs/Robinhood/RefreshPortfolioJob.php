<?php

namespace App\Jobs\Robinhood;

use App\Events\Portfolio\PortfolioUpdated;
use App\Jobs\BrowserJob;

class RefreshPortfolioJob extends BrowserJob
{
    /**
     * This method is to be overwritten in each Job in order to provide the correct
     * entry point in the construction of a job to add the tasks it will execute before
     * adding the jobs tags.
     *
     * @throws \Exception
     */
    public function setup()
    {
        $this->addTasks([
            new Tasks\LoginTask(),
            new Tasks\GrabPortfolioValues(),
            new Tasks\LogoutTask(),
        ]);
    }

    public function tearDown()
    {
        event(new PortfolioUpdated($this->user, $this->user->portfolio));
    }
}
