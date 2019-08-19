<?php


namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql','mysql_core'];

    /* @test */
    public function testUserIsRedirectedToVATSIM()
    {
        $this->get(route('login'))
            ->assertRedirect();
    }

    /* @test */
    public function testLoggedInUserRedirected()
    {
        $this->actingAs($this->user)
            ->get(route('login'))
            ->assertRedirect('/home');
    }

    /* @test */
    public function testSSOUserWithPasswordIsRedirected()
    {
        $this->user->setPassword("1234");

        $this->actingAs($this->user, 'partial_web')
            ->get(route('login'))
            ->assertRedirect(route('login.secondary'));
    }

    /* @test */
    public function testInvalidPasswordNotAccepted()
    {
        $this->user->setPassword("1234");

        $this->actingAs($this->user, 'partial_web')
            ->from(route('login.secondary'))
            ->post(route('login.secondary'), [
                'password' => 'abcd'
            ])
            ->assertSessionHas('error')
            ->assertLocation(route('login.secondary'));
    }

    /* @test */
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
