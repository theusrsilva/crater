<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    public function testIsInputsSet()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")->assertInputPresent("email")->assertInputPresent("password")->screenshot("testIsInputsSet");
        });
    }
}
