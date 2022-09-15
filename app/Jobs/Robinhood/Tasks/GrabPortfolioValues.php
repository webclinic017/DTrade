<?php

namespace App\Jobs\Robinhood\Tasks;

use App\Jobs\BrowserTask;
use App\Stock;
use App\Ticker;
use App\User;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;

class GrabPortfolioValues extends BrowserTask
{
    private function savePortfolioBuyingPower(User $user, Browser $browser)
    {
        $portfolioValues = $browser->elements('._3qq75So0cQbIZqkqc1aEj7 h3');
        $buyingPower = $portfolioValues[1]->getAttribute('innerHTML');
        $portfolio = $user->portfolio;
        $portfolio->cash = str_replace('$', '', $buyingPower);
        $portfolio->save();
    }

    private function updateStocks(User $user, Browser $browser)
    {
        $data = [];
        $stocks = [];
        $stockElements = $browser->elements('.rh-hyperlink.qD5a4psv-CV7GnWdHxLvn._3M7AljDD8LvhnX3nLSzsxK ._2I1BbQ7XdJcJvUV6xWz9u2');
        foreach ($stockElements as $stockElement) {
            $stockNameAndShares = $stockElement->findElements(WebdriverBy::cssSelector('._3HLJ3tNpwWnaSGO61Xz-VA'));
            $stockName = $stockNameAndShares[0]->getAttribute('innerHTML');
            $stockShares = $stockNameAndShares[1]->getAttribute('innerHTML');
            if (Ticker::symbolExists($stockName)) {
                $stock = Stock::fetch($stockName);
                $data[$stock->id] = ['shares' => intval(str_replace(' Shares', '', $stockShares))];
                array_push($stocks, strtolower($stock->symbol));
            }
        }

        $user->portfolio->stocks()->sync($data);

        $user->portfolio->stocks->each(function (Stock $stock) use ($stocks, $user) {
            if (!in_array(strtolower($stock->symbol), $stocks)) {
                \Log::debug('Deleting stock '.$stock->symbol);
                $user->portfolio->stocks()->detach($stock);
            }
        });
    }

    /**
     * @param User    $user
     * @param Browser $browser
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function execute(User $user = null, Browser $browser = null)
    {
        $currentUrl = $browser->driver->getCurrentURL();
        if ($currentUrl != 'https://robinhood.com/') {
            $browser->visit('https://robinhood.com/');
        }

        $browser->waitForText('Account', 10)
                ->click("a[href='/account']")
                ->waitForText('Portfolio Value', 10);

        $this->savePortfolioBuyingPower($user, $browser);

        $browser->click("a[href='/account']")
                ->pause(1000);

        $this->updateStocks($user, $browser);
    }
}
