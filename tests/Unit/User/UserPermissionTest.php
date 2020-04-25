<?php

namespace Tests\Unit\User;

use App\Events\User\PermissionsChanged;
use App\Exceptions\InvalidPermissionException;
use App\Models\Permissions\Assignment;
use App\Models\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use VATSIMUK\Support\Auth\Facades\PermissionValidity;

class UserPermissionTest extends TestCase
{
    use DatabaseTransactions;

    /* @var Role */
    private $role1;
    private $role2;
    private $role3;

    protected function setUp(): void
    {
        parent::setUp();

        PermissionValidity::partialMock()
            ->shouldReceive('isValidPermission')
            ->andReturn(true)->byDefault();

        $this->role1 = factory(Role::class)->create(['name' => 'Super Admin']);
        $this->role2 = factory(Role::class)->create(['name' => 'Member']);
        $this->role3 = factory(Role::class)->create(['name' => 'Non-Member']);

        factory(Assignment::class)->create(['related_id' => $this->role1, 'permission' => 'auth.permission.example']);
        factory(Assignment::class)->create(['related_id' => $this->role2, 'permission' => 'auth.permission.example2']);
        factory(Assignment::class)->create(['related_id' => $this->role3, 'permission' => 'auth.permission.example2']);
        factory(Assignment::class)->create(['related_id' => $this->role3, 'permission' => 'auth.permission.example3']);

        $this->user->roles()->sync([$this->role1->id, $this->role2->id]);
        factory(Assignment::class)->state('user')->create(['related_id' => $this->user->id, 'permission' => 'auth.permission.example']);
        factory(Assignment::class)->state('user')->create(['related_id' => $this->user->id, 'permission' => 'auth.permission.example4']);
        factory(Assignment::class)->state('user')->create(['related_id' => $this->user->id + 10, 'permission' => 'auth.permission.example5']);
        $this->user = $this->user->fresh();
    }

    /** @test */
    public function itDeletesAssignmentsWhenUserDeleted()
    {
        $this->user->permissions()->create(['permission' => 'auth.assign.false']);

        $this->user->delete();

        $this->assertDatabaseMissing('permission_assignments', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'permission' => 'auth.assign.false',
        ]);
    }

    /** @test */
    public function itCanHavePermissions()
    {
        $this->assertEquals(2, $this->user->permissions()->count());
        $this->assertEquals('auth.permission.example', $this->user->permissions()->first()->permission);
    }

    /** @test */
    public function itCanDetermineIfAUserHasAPermissionOrNot()
    {
        $this->assertTrue($this->user->hasPermissionTo('auth.permission.example'));
        $this->assertTrue($this->user->hasPermissionTo('auth.permission.example4'));
        $this->assertFalse($this->user->hasPermissionTo('auth.permission.example5'));
    }

    /** @test */
    public function itCanDetermineIfUserHasPermissionWithWildcard()
    {
        $this->user->permissions()->createMany([
            ['permission' => 'auth.bans.*'],
            ['permission' => 'auth.users.emails.*'],
        ]);
        $this->assertTrue($this->user->hasPermissionTo('auth.bans.add'));
        $this->assertTrue($this->user->hasPermissionTo('auth.bans.list.filter'));
        $this->assertTrue($this->user->hasPermissionTo('auth.users.emails'));

        $this->assertFalse($this->user->hasPermissionTo('auth.users.list'));

        $this->user->permissions()->createMany([
            ['permission' => '*'],
        ]);

        $this->assertTrue($this->user->hasPermissionTo('can.do.anything'));
    }

    /** @test */
    public function itCanDetermineIfHasAnyOfGivenPermissions()
    {
        $this->assertTrue($this->user->hasAnyPermission(['auth.permission.example', 'auth.permission.example5']));
        $this->assertFalse($this->user->hasAnyPermission(['auth.permission.example10', 'auth.permission.example5']));
    }

    /** @test */
    public function itCanDetermineIfHasAllOfGivenPermissions()
    {
        $this->assertTrue($this->user->hasAllPermissions(['auth.permission.example', 'auth.permission.example2']));
        $this->assertFalse($this->user->hasAllPermissions(['auth.permission.example', 'auth.permission.example5']));
        $this->assertFalse($this->user->hasAllPermissions(['auth.permission.example10', 'auth.permission.example5']));

        $assignment = factory(Assignment::class)->create(['permission' => 'auth.permission.*']);
        $this->user = $this->user->assignRole($assignment->related_id)->fresh();

        $this->assertTrue($this->user->hasAllPermissions(['auth.permission.example', 'auth.permission.example5']));
        $this->assertTrue($this->user->hasAllPermissions(['auth.permission.example10', 'auth.permission.example5']));
    }

    /** @test */
    public function itCanSeeIfItHasPermissionViaRole()
    {
        $this->assertTrue($this->user->hasPermissionViaRole('auth.permission.example2'));
        $this->assertFalse($this->user->hasPermissionViaRole('auth.permission.example4'));

        $assignment = factory(Assignment::class)->create(['permission' => 'auth.permission.extended.*']);
        factory(Assignment::class)->state('user')->create(['related_id' => $this->user->id, 'permission' => 'auth.permission.intended.*']);
        $this->user = $this->user->assignRole($assignment->related_id)->fresh();

        $this->assertTrue($this->user->hasPermissionViaRole('auth.permission.extended'));
        $this->assertTrue($this->user->hasPermissionViaRole('auth.permission.extended.example'));
        $this->assertFalse($this->user->hasPermissionViaRole('auth.permission.intended'));
        $this->assertTrue($this->user->hasPermissionTo('auth.permission.intended'));
    }

    /** @test */
    public function itCanSeeIfItHasPermissionDirectly()
    {
        $this->assertTrue($this->user->hasDirectPermission('auth.permission.example4'));
        $this->assertFalse($this->user->hasDirectPermission('auth.permission.example2'));

        $assignment = factory(Assignment::class)->create(['permission' => 'auth.permission.extended.*']);
        factory(Assignment::class)->state('user')->create(['related_id' => $this->user->id, 'permission' => 'auth.permission.intended.*']);
        $this->user = $this->user->assignRole($assignment->related_id)->fresh();

        $this->assertTrue($this->user->hasDirectPermission('auth.permission.intended'));
        $this->assertTrue($this->user->hasDirectPermission('auth.permission.intended.example'));
        $this->assertFalse($this->user->hasDirectPermission('auth.permission.extended'));
        $this->assertTrue($this->user->hasPermissionTo('auth.permission.extended'));
    }

    /** @test */
    public function itCanReturnListOfPermissionsViaRoles()
    {
        $this->assertEquals(collect(['auth.permission.example', 'auth.permission.example2']), $this->user->getPermissionsViaRoles());
    }

    /** @test */
    public function itCanReturnListOfAllPermissions()
    {
        $this->assertEquals(collect([
            'auth.permission.example',
            'auth.permission.example2',
            'auth.permission.example4',
        ]), $this->user->getAllPermissions());
    }

    /** @test */
    public function itCanAddPermissionsToIt()
    {
        Event::fake();

        $this->user->givePermissionTo('auth.test.*');
        $this->user->givePermissionTo(['auth.test.2', 'auth.test.3', '']);

        $this->assertTrue($this->user->hasAllPermissions(['auth.test.*', 'auth.test.2', 'auth.test.3']));
        $this->assertDatabaseHas('permission_assignments', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'permission' => 'auth.test.*',
        ]);
        $this->assertDatabaseHas('permission_assignments', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'permission' => 'auth.test.2',
        ]);
        $this->assertDatabaseHas('permission_assignments', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'permission' => 'auth.test.3',
        ]);

        Event::assertDispatchedTimes(PermissionsChanged::class, 2);
    }

    /** @test */
    public function itThrowsAnExceptionIfPermissionInvalid()
    {
        PermissionValidity::partialMock()
            ->shouldReceive('isValidPermission')
            ->andReturn(false);

        $this->expectException(InvalidPermissionException::class);

        $this->user->givePermissionTo('auth.test.*');
    }

    /** @test */
    public function itCanSyncPermissions()
    {
        Event::fake();

        $this->user->givePermissionTo('auth.test.1'); // Event #1
        $this->user->syncPermissions(['auth.test.2', 'auth.test.3']); // Event #2

        $this->assertTrue($this->user->hasAllPermissions(['auth.test.2', 'auth.test.3']));
        $this->assertFalse($this->user->hasPermissionTo('auth.test.1'));
        $this->assertDatabaseHas('permission_assignments', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'permission' => 'auth.test.2',
        ]);
        $this->assertDatabaseHas('permission_assignments', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'permission' => 'auth.test.3',
        ]);
        $this->assertDatabaseMissing('permission_assignments', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'permission' => 'auth.test.1',
        ]);
        Event::assertDispatchedTimes(PermissionsChanged::class, 2);
    }

    /** @test */
    public function itCanRevokeAPermission()
    {
        Event::fake();

        $this->assertTrue($this->user->hasPermissionTo('auth.permission.example4'));

        $this->user->revokePermissionTo('auth.permission.example4');

        $this->assertFalse($this->user->hasPermissionTo('auth.permission.example4'));
        Event::assertDispatchedTimes(PermissionsChanged::class, 1);
    }

    /** @test */
    public function itCantRevokeARolePermission()
    {
        Event::fake();

        $this->assertTrue($this->user->hasPermissionTo('auth.permission.example2'));

        $this->user->revokePermissionTo('auth.permission.example2');

        $this->assertTrue($this->user->hasPermissionTo('auth.permission.example2'));
        Event::assertNotDispatched(PermissionsChanged::class);
    }

    /** @test */
    public function itCanListLocalPermissions()
    {
        $this->assertEquals(collect(['auth.permission.example', 'auth.permission.example4']), $this->user->getPermissions());
    }

    /** @test */
    public function itDoesntDisplayDuplicates()
    {
        $this->assertEquals(1, $this->user->getAllPermissions()->filter(function ($value) {
            return $value == 'auth.permission.example';
        })->count());
    }
}
