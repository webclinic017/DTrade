<?php

namespace App\Jobs\Robinhood;

use App\Events\PortfolioUpdated;
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
            // @TODO Add tasks that actually grab the stocks in your portfolio so the value may be updated
            // Idea: Snag all links under the first section in [data-testid='InstrumentPreviewList']
            // With a brief test in jQuery, the selector "[data-testid='InstrumentPreviewList'] section" may work.
            // If not, this should "[data-testid='InstrumentPreviewList'] section:first-of-type"
            // This will change if they remove that testid.
            new Tasks\LogoutTask(),
        ]);
    }

    public function tearDown()
    {
        \Log::debug($this->user);
        event(new PortfolioUpdated($this->user));
    }
}
