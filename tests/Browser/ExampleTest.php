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
    public function testNotAuthBackToHome()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/admin/dashboard")->assertPathIs("/login")->screenshot("testIsInputsSet");
        });
    }
    public function testSetEmail()
    {
        $this->browse(function (Browser $browser) {
            $browser->waitFor('input[name=email]')
                ->script([
                    "document.querySelector('input[name=email]').value = 'email@teste.com.br';",
                ]);
            $browser->assertInputValue("email", 'email@teste.com.br');

        });
    }
    
}
