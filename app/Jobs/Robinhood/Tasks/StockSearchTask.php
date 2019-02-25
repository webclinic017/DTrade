<?php

namespace App\Jobs\Robinhood\Tasks;


use App\Jobs\BaseTask;
use App\User;
use Exception;
use Laravel\Dusk\Browser;

class StockSearchTask extends BaseTask
{
    public function setup()
    {
        $this->requiredParams = true;
    }

    /**
     * @param Browser $browser
     * @param User|null $user
     * @throws \Throwable
     */
    public function execute(Browser $browser, User $user = null)
    {
        $symbol = $this->params;
        if (!is_string($symbol)) throw new Exception('Ticker symbol must be sent as the parameter to the task upon initialization.');

        $browser->visit("https://robinhood.com/stocks/$symbol")
            ->waitForText(strtoupper($symbol),10);
    }
}