<?php

namespace Tests\Unit;

use App\Models\Permissions\Assignment;
use App\Models\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use DatabaseTransactions;

    private $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = factory(Role::class)->create();
    }

    /** @test */
    public function testItCanHavePermissions()
    {
        factory(Assignment::class)->create(['related_id' => $this->role->id, 'permission' => 'auth.permission.example']);
        factory(Assignment::class)->create(['related_id' => $this->role->id, 'permission' => 'auth.permission.example2']);

        $this->assertEquals(2, $this->role->permissions->count());
        $this->assertEquals('auth.permission.example', $this->role->permissions->first()->permission);

    }

    /** @test */
    public function testItCanHaveUsers()
    {
        $user2 = factory(User::class)->create();
        factory(User::class, 3)->create();

        $this->role->users()->sync([$this->user->id, $user2->id]);

        $this->assertEquals(2, $this->role->users->count());

    }
}
