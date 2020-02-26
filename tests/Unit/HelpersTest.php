<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    /** @test */
    public function testAuthenticatedOnAnyGuardFunction()
    {
        $this->assertFalse(authenticatedOnAnyGuard());

        Auth::login($this->user);
        $this->assertTrue(authenticatedOnAnyGuard());

        Auth::login($this->user, LoginController::SSO_GUARD);
        $this->assertTrue(authenticatedOnAnyGuard());

        Auth::logout();
        $this->assertFalse(authenticatedOnAnyGuard());

        Auth::guard(LoginController::SSO_GUARD)->login($this->user);
        $this->assertTrue(authenticatedOnAnyGuard());
    }

    public function testUserOnSSOGuardFunction()
    {
        $this->assertNull(userOnSSOGuard());
        $this->assertFalse(authenticatedOnSSOGuard());

        Auth::login($this->user);
        $this->assertNull(userOnSSOGuard());
        $this->assertFalse(authenticatedOnSSOGuard());

        Auth::guard(LoginController::SSO_GUARD)->login($this->user);
        $this->assertNotNull(userOnSSOGuard());
        $this->assertTrue(authenticatedOnSSOGuard());
    }

    public function testUserOnFullGuardFunction()
    {
        $this->assertNull(userOnFullGuard());
        $this->assertFalse(authenticatedOnFullGuard());

        Auth::guard(LoginController::SSO_GUARD)->login($this->user);
        $this->assertNull(userOnFullGuard());
        $this->assertFalse(authenticatedOnFullGuard());

        Auth::login($this->user);
        $this->assertNotNull(userOnFullGuard());
        $this->assertTrue(authenticatedOnFullGuard());

        Auth::logout();
        $this->assertNull(userOnFullGuard());
        $this->assertFalse(authenticatedOnFullGuard());

        Auth::guard(LoginController::FULL_GUARD)->login($this->user);
        $this->assertNotNull(userOnFullGuard());
        $this->assertTrue(authenticatedOnFullGuard());
    }
}
