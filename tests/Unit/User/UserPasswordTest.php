<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserPasswordTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanHaveAPasswordSet()
    {
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'password' => null,
        ]);

        Carbon::setTestNow(Carbon::now());
        $this->user->setPassword('test12345');

        $this->assertNotNull($this->user->password);
        $this->assertEquals(Carbon::now(), $this->user->password_set_at);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'password' => $this->user->password,
        ]);
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
