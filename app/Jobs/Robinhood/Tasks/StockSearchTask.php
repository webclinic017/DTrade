<?php

namespace App\Jobs\Robinhood\Tasks;

use App\Jobs\BrowserTask;
use App\User;
use Exception;
use Laravel\Dusk\Browser;

class StockSearchTask extends BrowserTask
{
    public function setup()
    {
        $this->requiredParams = true;
    }

    /**
     * @param User    $user
     * @param Browser $browser
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function execute(User $user = null, Browser $browser = null)
    {
        $symbol = $this->params;
        if (!is_string($symbol)) {
            throw new Exception('Ticker symbol must be sent as the parameter to the task upon initialization.');
        }
        $browser->visit("https://robinhood.com/stocks/$symbol")
                ->waitForText(strtoupper($symbol), 10);
    }
}
