<?php

namespace Tests\Feature;

use App\Models\Role;
use Carbon\Carbon;
use Tests\TestCase;

class UserRolePasswordRequirementTest extends TestCase
{
    const FAKE_PATH = '/some/path';

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = factory(Role::class)->create([
            'require_password' => true,
            'password_refresh_rate' => 30,
        ]);
        $this->user->syncRoles($this->role);
        $this->user = $this->user->fresh();
    }

    public function testUserWithRoleWithNoRequirementNotAffected()
    {
        $anotherRole = factory(Role::class)->create();
        $this->user->syncRoles($anotherRole);

        $this->actingAs($this->user)
            ->get(self::FAKE_PATH)
            ->assertOk();
    }

    public function testUserWithRequirementAndPasswordNotRedirected()
    {
        $this->user->setPassword('A Password');

        $this->actingAs($this->user)
            ->get(self::FAKE_PATH)
            ->assertOk();
    }

    public function testUserWithRequirementAndExpiredPasswordRedirected()
    {
        $this->user->setPassword('A Password');
        $this->user->password_set_at = Carbon::now()->subDays($this->role->password_refresh_rate + 1);

        $this->actingAs($this->user)
            ->get(self::FAKE_PATH)
            ->assertRedirect(route('login.password.set'));
    }

    public function testUserWithRequirementAndNoPasswordRedirected()
    {
        $this->actingAs($this->user)
            ->get(self::FAKE_PATH)
            ->assertRedirect(route('login.password.set'));
    }

    public function testUserCanSetThePasswordWithoutPassword()
    {
        $this->actingAs($this->user)
            ->followingRedirects()
            ->from(route('login.password.set'))
            ->post(route('login.password.set'), [
                'password' => 'A5trongP@ssw0rd',
                'password_confirmation' => 'A5trongP@ssw0rd',
            ])->assertLocation('/');

        $this->assertTrue($this->user->fresh()->verifyPassword('A5trongP@ssw0rd'));
    }

    public function testUserCanSetThePasswordWithPassword()
    {
        $this->user->setPassword('A Password');
        $this->user->password_set_at = Carbon::now()->subDays($this->role->password_refresh_rate + 1);

        $this->actingAs($this->user)
            ->followingRedirects()
            ->from(route('login.password.set'))
            ->post(route('login.password.set'), [
                'current_password' => 'A Password',
                'password' => 'A5trongP@ssw0rd',
                'password_confirmation' => 'A5trongP@ssw0rd',
            ])->assertLocation('/');

        $this->assertTrue($this->user->fresh()->verifyPassword('A5trongP@ssw0rd'));
    }
}
