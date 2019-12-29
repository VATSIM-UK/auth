<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\MustHavePassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Tests\TestCase;

class MustHavePasswordMiddlewareTest extends TestCase
{
    /** @test */
    public function itPassesIfTheyHavePassword()
    {
        $this->user->setPassword('Testing1234');
        $this->assertTrue($this->user->hasPassword());

        $this->actingAs($this->user);
        $request = new Request();
        $middleware = resolve(MustHavePassword::class);
        $response = $middleware->handle($request, function () {
        });

        $this->assertNull($response);

        $this->actingAs($this->user, 'partial_web');
        $request = new Request();
        $middleware = resolve(MustHavePassword::class);
        $response = $middleware->handle($request, function () {
        });

        $this->assertNull($response);
    }

    /** @test */
    public function itRedirectsIfTheyDontHavePassword()
    {
        $this->assertFalse($this->user->hasPassword());

        $this->actingAs($this->user);
        $request = new Request();
        $middleware = resolve(MustHavePassword::class);
        $response = $middleware->handle($request, function () {
        });

        $this->assertEquals(get_class($response), RedirectResponse::class);
    }

    /** @test */
    public function itThrowsExceptionIfNotAuthenticated()
    {
        $this->expectException(UnauthorizedException::class);

        $request = new Request();
        $middleware = resolve(MustHavePassword::class);
        $middleware->handle($request, function () {
        });
    }
}
