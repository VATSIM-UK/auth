<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testAuthenticatedOnAnyGuardFunction()
    {
        $this->assertFalse(authenticatedOnAnyGuard());

        Auth::login($this->user);
        $this->assertTrue(authenticatedOnAnyGuard());

        Auth::login($this->user, 'partial_web');
        $this->assertTrue(authenticatedOnAnyGuard());

        Auth::logout();
        $this->assertFalse(authenticatedOnAnyGuard());

        Auth::guard('partial_web')->login($this->user);
        $this->assertTrue(authenticatedOnAnyGuard());
    }
}