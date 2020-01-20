<?php

namespace Tests\Unit;

use App\Models\Role;
use Carbon\Carbon;
use Tests\TestCase;

class UserPasswordTest extends TestCase
{
    /** @test */
    public function itCanHaveAPasswordSet()
    {
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'password' => null,
        ]);

        Carbon::setTestNow(Carbon::now()->setMicro(0));
        $this->user->setPassword('test12345');

        $this->assertNotNull($this->user->password);
        $this->assertEquals(Carbon::now(), $this->user->password_set_at);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'password' => $this->user->password,
        ]);
    }

    /** @test */
    public function itCanDetermineIfPasswordHasExpired()
    {
        Carbon::setTestNow($passwordSetTime = Carbon::now()->setMicro(0));
        $this->user->setPassword('test12345');
        $role = factory(Role::class)->create([
            'require_password' => true,
            'password_refresh_rate' => 10,
        ]);
        $this->user->syncRoles($role);

        $this->assertTrue($this->user->requiresPassword());
        $this->assertEquals($passwordSetTime->addDays(10), $this->user->password_expires_at);

        $this->assertFalse($this->user->passwordHasExpired());
        Carbon::setTestNow($passwordSetTime->addDays(10));
        $this->assertTrue($this->user->passwordHasExpired());
    }

    /** @test */
    public function itCanHaveAPasswordVerified()
    {
        $this->user->setPassword('test12345');

        $this->assertTrue($this->user->verifyPassword('test12345'));
        $this->assertFalse($this->user->verifyPassword('wrongPa55w0rd'));
    }

    /** @test */
    public function itCanHavePassword()
    {
        $this->assertNull($this->user->password);
        $this->assertFalse($this->user->hasPassword());

        $this->user->setPassword('test12345');
        $this->assertTrue($this->user->hasPassword());
    }

    /** @test */
    public function itCanHavePasswordRemoved()
    {
        $this->user->setPassword('test12345');
        $this->assertNotNull($this->user->password);

        $this->user->removePassword();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'password' => null,
            'password_set_at' => null,
        ]);
    }
}
