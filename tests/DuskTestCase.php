<?php

namespace Tests;

use Derekmd\Dusk\Concerns\TogglesHeadlessMode;
use Derekmd\Dusk\Firefox\SupportsFirefox;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication, SupportsFirefox, TogglesHeadlessMode;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        // if (! static::runningFirefoxInSail()) {
        //     static::startFirefoxDriver();
        // }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $server = 'http://selenium:4444';

        $options = (new ChromeOptions)->addArguments([
            '--window-size=1920,1080',
            '--whitelisted-ips=""',
            '--enable-file-cookies'
        ]);

        $capabilities = DesiredCapabilities::chrome()
            ->setCapability(ChromeOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(
            $server,
            $capabilities,
        );
    }
}
