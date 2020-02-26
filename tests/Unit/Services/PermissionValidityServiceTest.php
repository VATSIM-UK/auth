<?php

namespace Tests\Unit\Services;

use App\Models\Permissions\Assignment;
use Tests\TestCase;
use VATSIMUK\Support\Auth\Facades\PermissionValidity;

class PermissionValidityServiceTest extends TestCase
{
    /** @test */
    public function itCanLoadPermissionsFile()
    {
        $this->assertFalse(PermissionValidity::isValidPermission('test.permission.that.doesnt.exist'));
    }

    /** @test */
    public function itReportsIfPermissionIsGrantedFromListOfHeldPermissions()
    {
        $assignment = factory(Assignment::class)->create([
            'permission' => 'auth.permissions.view',
        ]);
        factory(Assignment::class)->create([
            'related_id' => $assignment->related_id,
            'permission' => 'auth.users.*',
        ]);
        factory(Assignment::class, 'user')->create([
            'related_id' => $this->user->id,
            'permission' => 'auth.permissions.view',
        ]);
        factory(Assignment::class, 'user')->create([
            'related_id' => $this->user->id,
            'permission' => 'auth.users.*',
        ]);

        // MorphsMany Permissions Relationship
        $this->assertTrue(PermissionValidity::permissionSatisfiedByPermissions('auth.users.create', $this->user->permissions()));
        $this->assertTrue(PermissionValidity::permissionSatisfiedByPermissions('auth.users.create', $assignment->related->permissions()));

        $this->assertFalse(PermissionValidity::permissionSatisfiedByPermissions('auth.users.create', []));
    }

    /** @test */
    public function itAllowsTopLevelPermissionIfChildHeld()
    {
        PermissionValidity::partialMock()
            ->shouldReceive('isValidPermission')
            ->andReturn(true);
        $this->assertFalse($this->user->can('auth.permissions'));
        $this->user->givePermissionTo('auth.permissions.assign');
        $this->assertTrue($this->user->can('auth.permissions'));
        $this->assertFalse($this->user->can('auth.permissions.create'));
    }
}
