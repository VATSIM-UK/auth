<?php

namespace Tests\Unit;

use App\Events\User\PermissionsChanged;
use App\Models\Permissions\Assignment;
use App\Models\Role;
use App\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use VATSIMUK\Support\Auth\Facades\PermissionValidity;

class RoleTest extends TestCase
{
    /* @var Role */
    private $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = factory(Role::class)->create();
    }

    /** @test */
    public function itCanHaveUsers()
    {
        $user2 = factory(User::class)->create();
        factory(User::class, 3)->create();

        $this->role->users()->sync([$this->user->id, $user2->id]);

        $this->assertEquals(2, $this->role->users->count());
    }

    /** @test */
    public function itCanHavePermissions()
    {
        factory(Assignment::class)->create(['related_id' => $this->role->id, 'permission' => 'auth.permission.example']);
        factory(Assignment::class)->create(['related_id' => $this->role->id, 'permission' => 'auth.permission.example2']);

        $this->assertEquals(2, $this->role->permissions->count());
        $this->assertEquals('auth.permission.example', $this->role->permissions->first()->permission);
    }

    /** @test */
    public function itCanAddPermissions()
    {
        PermissionValidity::partialMock()
            ->shouldReceive('isValidPermission')
            ->andReturn(true);
        Event::fake();

        $this->role->givePermissionTo('do.one.thing');

        Event::assertNotDispatched(PermissionsChanged::class);
    }

    /** @test */
    public function itCanRemovePermissions()
    {
        factory(Assignment::class)->create(['related_id' => $this->role->id, 'permission' => 'do.one.thing']);
        Event::fake();

        $this->role->revokePermissionTo('do.one.thing');

        Event::assertNotDispatched(PermissionsChanged::class);
    }

    /** @test */
    public function itCanBeFoundByName()
    {
        $anotherRole = factory(Role::class)->create(['name' => 'My Custom Role']);

        $this->assertEquals($anotherRole->id, Role::findByName('My Custom Role')->id);
    }

    /** @test */
    public function itCanTellIfItHasPermissions()
    {
        factory(Assignment::class)->create(['related_id' => $this->role->id, 'permission' => 'auth.permission.example']);

        $this->assertTrue($this->role->hasPermissionTo('auth.permission.example'));
        $this->assertFalse($this->role->hasPermissionTo('auth.permission.example2'));
    }
}
