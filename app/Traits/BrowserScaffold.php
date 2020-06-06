<?php

namespace App\Traits;

use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\SupportsChrome;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Tests\CreatesApplication;

trait BrowserScaffold
{
    use CreatesApplication;
    use ProvidesBrowser;
    use SupportsChrome;

    private $basePath;

    public function __construct()
    {
        $this->createApplication();

        $this->basePath = base_path('tests/Browser/');

        \Log::debug($this->basePath);

        static::startChromeDriver();

        Browser::$baseUrl = config('app.url');

        Browser::$storeScreenshotsAt = base_path('tests/Browser/screenshots');

        Browser::$storeConsoleLogAt = base_path('tests/Browser/console');
    }

    public static function getName()
    {
        return 'BrowserScaffold';
    }

    private function storeLogs($browsers, $path, $prefix = '')
    {
        $browsers->each(function ($browser, $key) use ($path, $prefix) {
            $path = base_path($path);
            $name = str_replace('\\', '_', get_class($this)).'_'.$this->getName(false);
            $browser->screenshot("$path/$prefix$name-$key");
        });
    }

    /**
     * Capture failure screenshots for each browser.
     *
     * @param \Illuminate\Support\Collection $browsers
     *
     * @return void
     */
    protected function captureFailuresFor($browsers)
    {
        $this->storeLogs($browsers, 'tests/Browser/screenshots', 'failure-');
    }

    /**
     * Store the console output for the given browsers.
     *
     * @param \Illuminate\Support\Collection $browsers
     *
     * @return void
     */
    protected function storeConsoleLogsFor($browsers)
    {
        $this->storeLogs($browsers, 'tests/Browser/console', 'failure-');
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = new ChromeOptions();
        $options->addArguments([
            '--window-size=1920,1080',
            '--no-sandbox',
        ]);

        if (env('APP_ENV') === 'production') {
            $options->addArguments([
                '--disable-gpu',
                '--headless',
            ]);
        }

        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);
        $caps->setPlatform(env('APP_PLATFORM'));

        $host = 'http://localhost:9515';

        return RemoteWebDriver::create($host, $caps);
    }

    /**
     * Determine the application's base URL.
     *
     * @return string
     */
    protected function baseUrl()
    {
        return config('app.url');
    }

    /**
     * Return the default user to authenticate.
     *
     * @throws \Exception
     *
     * @return \App\User|int|null
     */
    protected function user()
    {
        throw new Exception('User resolver has not been set.');
    }
}
