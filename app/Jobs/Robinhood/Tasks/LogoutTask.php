<?php

namespace App\Jobs\Robinhood\Tasks;

use App\Jobs\BrowserTask;
use App\User;
use Laravel\Dusk\Browser;

class LogoutTask extends BrowserTask
{
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
                ->waitForText('Portfolio Value', 10)
                ->assertSeeIn("a[href='/login']", 'Log Out')
                ->click("a[href='/login']");
    }
}
