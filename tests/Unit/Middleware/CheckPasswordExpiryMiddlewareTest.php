<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckForExpiredPasswords;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckPasswordExpiryMiddlewareTest extends TestCase
{
    private $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = factory(Role::class)->create([
            'require_password' => true,
            'password_refresh_rate' => 10,
        ]);
    }

    /** @test */
    public function itPassesIfTheyHaveNoPasswordAndAreNotRequired()
    {
        $this->actingAs($this->user);
        $request = new Request();
        $middleware = resolve(CheckForExpiredPasswords::class);
        $response = $middleware->handle($request, function () {
            //
        });

        $this->assertNull($response);
    }

    /** @test */
    public function itPassesIfTheyHavePasswordButNotExpired()
    {
        $this->user->syncRoles($this->role);
        $this->user->setPassword('123');
        $this->actingAs($this->user);
        $request = new Request();
        $middleware = resolve(CheckForExpiredPasswords::class);
        $response = $middleware->handle($request, function () {
            //
        });

        $this->assertNull($response);
    }

    /** @test */
    public function itRedirectsIfTheyAreRequiredToSetPasswordAndDontHaveOne()
    {
        $this->user->syncRoles($this->role);

        $this->actingAs($this->user);
        $request = new Request();
        $middleware = resolve(CheckForExpiredPasswords::class);

        $response = $middleware->handle($request, function () {
            //
        });
        $this->assertEquals(get_class($response), RedirectResponse::class);
        $this->assertEquals(route('login.password.set'), $response->headers->get('location'));
    }

    /** @test */
    public function itRedirectsIfTheyAreRequiredToRefreshPassword()
    {
        $this->user->syncRoles($this->role);
        $this->user->setPassword('123');
        $this->user->password_set_at = Carbon::now()->subDays(11);

        $this->actingAs($this->user);
        $request = new Request();
        $middleware = resolve(CheckForExpiredPasswords::class);

        $response = $middleware->handle($request, function () {
            //
        });
        $this->assertEquals(get_class($response), RedirectResponse::class);
        $this->assertEquals(route('login.password.set'), $response->headers->get('location'));
    }
}
