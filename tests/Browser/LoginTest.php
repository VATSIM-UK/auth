<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testItCanLogout()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/')
                ->dump()
                ->assertSeeLink('Logout')
                ->clickLink('Logout')
                ->assertPathIs('/')
                ->assertSeeLink('Login')
                ->assertGuest();
        });

        $this->user->password = 'Test123';
        $this->user->save();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user, 'partial_web')
                ->visit(route('login.secondary'))
                ->assertSeeLink('Logout')
                ->clickLink('Logout')
                ->assertPathIs('/')
                ->assertSeeLink('Login')
                ->assertGuest();
        });
    }

    public function testItCanBeRedirectedToSSOAndRedirectedBack()
    {
        $user = factory(User::class)->create([
            'id' => 1300001,
            'password' => 'Test123',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->clickLink('Login')
                ->assertUrlIs(env('VATSIM_SSO_BASE').'auth/login/')
                ->type('cid', 1300001)
                ->type('password', 1300001)
                ->press('Login')
                ->assertUrlIs(route('login.secondary'))
                ->screenshot('login/secondary_authentication')
                ->assertSee('Secondary Authentication')
                ->type('password', 'Test123')
                ->press('Login')
                ->assertPathIs('/')
                ->assertAuthenticated();
        });
    }

    public function testItCanCheckForNoSecondaryPassword()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user, 'partial_web')
                ->visit(route('login'))
                ->assertPathIs('/')
                ->assertAuthenticated('web');
        });
    }

    public function testItCanChecksSecondaryPassword()
    {
        $this->user->password = 'Test123';
        $this->user->save();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user, 'partial_web')
                ->visit(route('login'))
                ->type('password', 'Test1234')
                ->press('Login')
                ->screenshot('login/secondary_authentication_error')
                ->assertSee('password did not match our records')
                ->assertUrlIs(route('login.secondary'));
        });
    }
}
