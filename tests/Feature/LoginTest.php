<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginTest extends TestCase
{
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

    public function testUserIsRedirectedToVATSIM()
    {
        $this->get(route('login'))
            ->assertRedirect();
    }

    public function testLoggedInUserRedirected()
    {
        $this->actingAs($this->user)
            ->get(route('login'))
            ->assertRedirect('/');
    }

    public function testSSOUserRedirectedIfNoPassword()
    {
        $this->actingAs($this->user, 'partial_web')
            ->followingRedirects()
            ->get(route('login'));

        $this->assertAuthenticatedAs($this->user, 'web');
    }

    public function testSSOUserCanSeeSignin()
    {
        $this->user->setPassword('1234');

        $this->actingAs($this->user, 'partial_web')
            ->get(route('login'))
            ->assertSuccessful()
            ->assertSeeText('Secondary Authentication');
    }

    public function testInvalidPasswordNotAccepted()
    {
        $this->user->setPassword('1234');

        $this->actingAs($this->user, 'partial_web')
            ->from(route('login'))
            ->post(route('login'), [
                'password' => 'abcd',
            ])
            ->assertSessionHasErrors()
            ->assertLocation(route('login'));
    }

    public function testValidPasswordAccepted()
    {
        $this->user->setPassword('1234');

        $this->actingAs($this->user, 'partial_web')
            ->from(route('login'))
            ->post(route('login'), [
                'password' => '1234',
            ])->assertRedirect('/');
    }
}
