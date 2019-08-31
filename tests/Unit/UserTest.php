<?php


namespace Tests\Unit;


use Carbon\Carbon;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function itCanHaveAPasswordSet(){
        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'password' => null,
        ], 'mysql_core');

        Carbon::setTestNow(Carbon::now());
        $this->user->setPassword('test12345');

        $this->assertNotNull($this->user->password);
        $this->assertEquals(Carbon::now(), $this->user->password_set_at);

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'password' => $this->user->password,
        ], 'mysql_core');
    }

    /** @test */
    public function itCanHaveAPasswordVerified(){
        $this->user->setPassword('test12345');

        $this->assertTrue($this->user->verifyPassword('test12345'));
        $this->assertFalse($this->user->verifyPassword('wrongPa55w0rd'));
    }

    /** @test */
    public function itCanHavePassword(){
        $this->assertNull($this->user->password);
        $this->assertFalse($this->user->hasPassword());

        $this->user->setPassword('test12345');
        $this->assertTrue($this->user->hasPassword());
    }

    /** @test */
    public function itCanHavePasswordRemoved(){
        $this->user->setPassword('test12345');
        $this->assertNotNull($this->user->password);

        $this->user->removePassword();

        $this->assertDatabaseHas('mship_account', [
            'id' => $this->user->id,
            'password' => null,
            'password_set_at' => null,
        ], 'mysql_core');
    }
}
