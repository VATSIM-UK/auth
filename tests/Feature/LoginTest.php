<?php


namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql','mysql_core'];

    /** @test */
    public function testUserCanLogout()
    {
        $this->actingAs($this->user, 'web');
        $this->assertAuthenticated('web');

        $this->followingRedirects()
            ->get(route('logout'))
            ->assertOk();

        $this->assertGuest();

        $this->actingAs($this->user, 'partial_web');
        $this->assertAuthenticated('partial_web');

        $this->followingRedirects()
            ->get(route('logout'))
            ->assertOk();

        $this->assertGuest();
    }

    /** @test */
    public function testUserIsRedirectedToVATSIM()
    {
        $this->get(route('login'))
            ->assertRedirect();
    }

    /** @test */
    public function testLoggedInUserRedirected()
    {
        $this->actingAs($this->user)
            ->get(route('login'))
            ->assertRedirect('/home');
    }

    /** @test */
    public function testSSOUserWithPasswordIsRedirected()
    {
        $this->user->setPassword("1234");

        $this->actingAs($this->user, 'partial_web')
            ->get(route('login'))
            ->assertRedirect(route('login.secondary'));
    }

    /** @test */
    public function testSSOUserCanSeeSignin()
    {
        $this->user->setPassword("1234");

        $this->actingAs($this->user, 'partial_web')
            ->get(route('login.secondary'))
            ->assertSuccessful()
            ->assertSeeText('Secondary Authentication');
    }

    /** @test */
    public function testInvalidPasswordNotAccepted()
    {
        $this->user->setPassword("1234");

        $this->actingAs($this->user, 'partial_web')
            ->from(route('login.secondary'))
            ->post(route('login.secondary'), [
                'password' => 'abcd'
            ])
            ->assertSessionHasErrors()
            ->assertLocation(route('login.secondary'));
    }

    /** @test */
    public function testValidPasswordAccepted()
    {
        $this->user->setPassword("1234");

        $this->actingAs($this->user, 'partial_web')
            ->from(route('login.secondary'))
            ->post(route('login.secondary'), [
                'password' => '1234'
            ])->assertRedirect('/home');
    }


}
