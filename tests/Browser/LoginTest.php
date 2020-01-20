<?php

namespace Tests\Browser;

use App\Http\Controllers\Auth\LoginController;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    private $fakePassword = 'Test123';

    public function testItCanLogout()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/')
                ->assertSeeLink('Logout')
                ->clickLink('Logout')
                ->assertPathIs('/')
                ->assertSeeLink('Login')
                ->assertGuest();
        });

        $this->user->password = $this->fakePassword;
        $this->user->save();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user, LoginController::SSO_GUARD)
                ->visit(route('login'))
                ->assertSeeLink('Logout')
                ->clickLink('Logout')
                ->assertPathIs('/')
                ->assertSeeLink('Login')
                ->assertGuest();
        });
    }

    public function testItCanBeRedirectedToSSOAndRedirectedBack()
    {
        factory(User::class)->create([
            'id' => 1300001,
            'password' => $this->fakePassword,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->clickLink('Login')
                ->assertUrlIs(env('VATSIM_SSO_BASE').'auth/login/')
                ->type('cid', 1300001)
                ->type('password', 1300001)
                ->press('Login')
                ->assertUrlIs(route('login'))
                ->screenshot('login/secondary_authentication')
                ->assertSee('Secondary Authentication')
                ->type('password', $this->fakePassword)
                ->press('Login')
                ->assertPathIs('/')
                ->assertAuthenticated();
        });
    }

    public function testItCanCheckForNoSecondaryPassword()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user, LoginController::SSO_GUARD)
                ->visit(route('login'))
                ->assertPathIs('/')
                ->assertAuthenticated('web');
        });
    }

    public function testItCanChecksSecondaryPassword()
    {
        $this->user->password = $this->fakePassword;
        $this->user->save();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user, LoginController::SSO_GUARD)
                ->visit(route('login'))
                ->type('password', "{$this->fakePassword}4")
                ->press('Login')
                ->screenshot('login/secondary_authentication_error')
                ->assertSee('password did not match our records')
                ->assertUrlIs(route('login'));
        });
    }
}
