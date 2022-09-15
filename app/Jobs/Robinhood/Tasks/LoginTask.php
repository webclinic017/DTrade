<?php

namespace App\Jobs\Robinhood\Tasks;

use App\Events\Robinhood\MultiFactorFailed;
use App\Events\Robinhood\MultiFactorNecessary;
use App\Jobs\BrowserTask;
use App\MFACode;
use App\PlatformData;
use App\User;
use Carbon\Carbon;
use Exception;
use Facebook\WebDriver\Exception\TimeOutException;
use Laravel\Dusk\Browser;

class LoginTask extends BrowserTask
{
    public $codeInputSelector = 'input[placeholder="000000"]';
    public $robinhoodAccount;
    public $browser;
    public $user;

    private function tryMfaCode($code)
    {
        try {
            $this->browser->type($this->codeInputSelector, $code)
                          ->press('Confirm')
                          ->waitForText('Account');

            return true;
        } catch (TimeOutException $e) {
        }

        return false;
    }

    private function isCurrentNewer($current, $previous)
    {
        if ($current instanceof MFACode && $previous instanceof MFACode) {
            return $current->created_at > $previous->created_at;
        }

        return false;
    }

    private function getMfaCode()
    {
        return User::where('id', $this->user->id)->first()->mfaCode()->first();
    }

    private function mfaFailure()
    {
        $message = 'did not supply a MFA token in the time allotted.';
        $this->browser->quit();
        event(new MultiFactorFailed($this->user, "You $message"));

        throw new Exception("User $message");
    }

    private function handleMfa($mfaAttempted = false, $previousMfaCode = null, $tries = 0)
    {
        if ($tries >= 60) {
            // Timeout waiting for user to free up queue worker after 2 minutes.
            $this->mfaFailure();

            return false;
        }

        $mfaCode = $this->getMfaCode();
        if ($this->isCurrentNewer($mfaCode, $previousMfaCode)) {
            $mfaAttempted = false;
        }

        $mfaCodeIsRecent = $mfaCode && $mfaCode->created_at->diffInSeconds() < 10;
        if ($mfaCodeIsRecent && !$mfaAttempted) {
            // A recent MFA Code exists for a user and it has not already been attempted
            if ($this->tryMfaCode($mfaCode->code)) {
                return true;
            } else {
                // Code was invalid, notify the user to try again
                $mfaAttempted = true;
                event(new MultiFactorNecessary($this->user, 'That code was invalid, please try again.'));
            }
        }

        $tries += 1;
        $previousMfaCode = $mfaCode;

        sleep(2);

        return $this->handleMfa($mfaAttempted, $previousMfaCode, $tries);
    }

    private function waitForAccountText()
    {
        try {
            $this->browser->waitForText('Account');
        } catch (TimeOutException $e) {
            $userId = $this->user->id;
            $dateString = Carbon::now()->format('yy-m-d-His');
            $errorTitle = 'Account-Text-Not-Visible';
            $this->browser->screenshot("failure-$errorTitle--user-$userId--$dateString");
            $this->browser->quit();

            throw new Exception('Account not visible when it should be');
        }
    }

    private function clickTextOrEmailOption()
    {
        try {
            $this->browser
                ->assertSee('Text Me')
                ->press('Text Me')
                ->waitFor($this->codeInputSelector);

            return 'phone';
        } catch (TimeOutException $e) {
            $this->browser
                ->assertSee('Email Me')
                ->press('Email Me')
                ->waitFor($this->codeInputSelector);

            return 'email';
        }
    }

    private function sendMfaRequest()
    {
        $verificationMethod = $this->clickTextOrEmailOption();
        event(new MultiFactorNecessary($this->user, "Please enter the MFA code you were just sent to your $verificationMethod below."));
    }

    private function typeUsernamePassword()
    {
        $this->browser->type("input[name='username']", $this->robinhoodAccount->username)
                      ->type("input[name='password']", decrypt($this->robinhoodAccount->password))
                      ->click("button[type='submit']");
    }

    private function has($text)
    {
        try {
            $this->browser->waitForText($text);

            return true;
        } catch (TimeOutException $e) {
        }

        return false;
    }

    private function needsMfa()
    {
        return $this->has('Verify Your Identity');
    }

    private function needsLogin()
    {
        return $this->has('Welcome to Robinhood');
    }

    private function getRobinhoodAccount()
    {
        $robinhoodAccount = $this->user->platforms()->where('platform', 'robinhood')->first();
        if ($robinhoodAccount == null) {
            throw new Exception('User does not have a linked Robinhood account');
        }

        return $robinhoodAccount;
    }

    public function getCookies(Browser $browser, PlatformData $robinhood)
    {
        if ($robinhood->cookies) {
            //Add each cookie to this session
            $cookies = collect(unserialize(base64_decode($robinhood->cookies->data)));

            $cookies->each(function ($cookie) use ($browser) {
                $browser->driver->manage()->addCookie($cookie);
            });
        }
    }

    public function saveCookies(Browser $browser, PlatformData $robinhood)
    {
        $cookies = base64_encode(serialize($browser->driver->manage()->getCookies()));
        if (!$robinhood->cookies) {
            $robinhood->cookies()->create(['data' => $cookies]);
        } else {
            $cookiesObject = $robinhood->cookies;
            $cookiesObject->data = $cookies;
            $cookiesObject->save();
        }
    }

    private function visitHomePage(Browser $browser)
    {
        $browser->visit('https://robinhood.com/')
                ->waitForText('Investing for Everyone');
    }

    private function visitLoginPage(Browser $browser)
    {
        try {
            $browser->clickLink('Log In')
                ->waitForText('Welcome to Robinhood', 30);
        } catch (TimeOutException $e) {
        }
    }

    /**
     * @param User    $user
     * @param Browser $browser
     *
     * @throws Exception
     */
    public function execute(User $user = null, Browser $browser = null)
    {
        $this->user = $this->expectsUser($user);
        $this->browser = $this->expectsBrowser($browser);
        $this->robinhoodAccount = $this->getRobinhoodAccount();

        $this->visitHomePage($browser);
        $this->getCookies($browser, $this->robinhoodAccount);
        $this->visitLoginPage($browser);

        if ($this->needsLogin()) {
            $this->typeUsernamePassword();

            if ($this->needsMfa()) {
                $this->sendMfaRequest();
                $this->handleMfa();
            }
        }

        $this->waitForAccountText();

        $this->saveCookies($browser, $this->robinhoodAccount);
        $this->robinhoodAccount->last_login = Carbon::now()->toDateTimeString();
        $this->robinhoodAccount->save();
    }
}
