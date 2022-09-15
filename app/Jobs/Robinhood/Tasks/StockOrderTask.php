<?php

namespace App\Jobs\Robinhood\Tasks;

use App\Jobs\BrowserTask;
use App\User;
use Exception;
use Laravel\Dusk\Browser;

class StockOrderTask extends BrowserTask
{
    public function setup()
    {
        $this->requiredParams = ['order', 'order_type', 'ticker', 'shares'];
    }

    /**
     * @param Browser $browser
     * @param User    $user
     * @param $order
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    private function submitOrder(Browser $browser, User $user, $order)
    {
        $browser->press('Review Order')
                ->waitForText('You are')
                ->press("$order");

        // Handle the cases where robinhood requires you reenter your password.
        if ($browser->element("input[name='password']")) {
            $robinhoodLogin = $user->platforms()->where('platform', 'robinhood')->first();
            $browser->type("input[name='password']", decrypt($robinhoodLogin->password))->press('Continue');
        }
    }

    public function typeStopPrice(Browser $browser)
    {
        $browser->type("input[name='stopPrice']", $this->params['stop_price']);
    }

    public function typeLimitPrice(Browser $browser)
    {
        $browser->type('input[name="limitPrice"]', $this->params['limit_price']);
    }

    public function typeShares(Browser $browser)
    {
        $browser->type("input[name='quantity']", $this->params['shares']);
    }

    public function selectExpiration(Browser $browser)
    {
        $browser->click('button.-aVQMh2t1ihTiMYRyjQA2')
                ->clickLink($this->params['expiration'], '._39ED64wnAoahW3-2WXpEzU');
    }

    public function handleMarketOrder(Browser $browser)
    {
        if ($browser->element('button.-aVQMh2t1ihTiMYRyjQA2')) {
            $browser->click('button.-aVQMh2t1ihTiMYRyjQA2')
                ->clickLink('Shares', '._2P2xUGg0JyC-C4gA3ucexl');
        }
        $this->typeShares($browser);
    }

    public function handleLimitOrder(Browser $browser)
    {
        $this->addRequiredParams('limit_price');
        $this->typeLimitPrice($browser);
        $this->typeShares($browser);
        $this->selectExpiration($browser);
    }

    public function handleStopLossOrder(Browser $browser)
    {
        $this->addRequiredParams('stop_price');
        $this->typeStopPrice($browser);
        $this->typeShares($browser);
        $this->selectExpiration($browser);
    }

    public function handleStopLimitOrder(Browser $browser)
    {
        $this->addRequiredParams(['limit_price', 'stop_price']);
        $this->typeStopPrice($browser);
        $this->typeLimitPrice($browser);
        $this->typeShares($browser);
        $this->selectExpiration($browser);
    }

    /**
     * handleOrder - parse the order type to a handler and execute it.
     *
     * @param Browser $browser
     * @param         $orderType
     *
     * @throws Exception
     */
    public function handleOrder(Browser $browser, $orderType)
    {
        $orderHandler = 'handle'.str_replace(' ', '', ucwords($orderType)).'Order';
        if (method_exists($this, $orderHandler)) {
            $this->$orderHandler($browser);
        } else {
            throw new Exception("Order Handler $orderHandler does not exist.");
        }
    }

    public function selectOrderType(Browser $browser, $orderType)
    {
        // Choose the correct order; Buy or Sell
        $buyOrSell = ucfirst($this->params['order']);
        $symbol = strtoupper($this->params['ticker']);
        $orderLink = $buyOrSell.' '.$symbol;

        if ($browser->seeLink($orderLink)) {
            $browser->clickLink($orderLink) // Click either Buy or Sell
                    ->pause(250); // Wait for menu
        }

        $browser->click('._3C94FbH6M2syYjD4BhzwBF.css-1tw7eno.e1c2vvtl0') // Click the carrot menu
                ->waitForText('Order Type') // Wait for the dialog to appear
                ->clickLink(ucwords("$orderType Order"), '._2_dmPaZj4NEDskSalJmSIh._1hH9lEYaPCvkiULeVG3Jjr[role="button"] span') // Click on the correct order
                ->pause(500); // Wait for menu
    }

    /**
     * @param User    $user
     * @param Browser $browser
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws Exception
     */
    public function execute(User $user = null, Browser $browser = null)
    {
        $ticker = strtoupper($this->params['ticker']);
        $orderType = $this->params['order_type'];

        $browser->visit("https://robinhood.com/stocks/$ticker")
                ->waitForText($ticker);

        $this->selectOrderType($browser, $orderType);

        $this->handleOrder($browser, $orderType);

        // Done filling out the order, try to submit it!
        $this->submitOrder($browser, $user, ucfirst($this->params['order']));
    }
}
