<?php

namespace Tests\Unit;

use App\Models\Permissions\Assignment;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserPermissionRoleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $role1 = factory(Role::class)->create(['name' => 'Super Admin']);
        $role2 = factory(Role::class)->create(['name' => 'Member']);
        $role3 = factory(Role::class)->create(['name' => 'Non-Member']);

        factory(Assignment::class)->create(['related_id' => $role1, 'permission' => 'auth.permission.example']);
        factory(Assignment::class)->create(['related_id' => $role2, 'permission' => 'auth.permission.example2']);
        factory(Assignment::class)->create(['related_id' => $role3, 'permission' => 'auth.permission.example3']);

        $this->user->roles()->sync([$role1->id, $role2->id]);
        factory(Assignment::class, 'user')->create(['related_id' => $this->user->id, 'permission' => 'auth.permission.example4']);
        factory(Assignment::class, 'user')->create(['related_id' => $this->user->id, 'permission' => 'auth.permission.example']);
        factory(Assignment::class, 'user')->create(['related_id' => $this->user->id + 10, 'permission' => 'auth.permission.example5']);
        $this->user = $this->user->fresh();
    }

    /** @test */
    public function itCanHaveRoles()
    {
        $this->assertEquals(2, $this->user->roles->count());
        $this->assertCollectionSubset(collect(['Super Admin', 'Member']), $this->user->roles->pluck('name'));
    }

    /** @test */
    public function itCanHaveSoloPermissions()
    {
        $this->assertEquals(2, $this->user->soloPermissions->count());
        $this->assertEquals('auth.permission.example', $this->user->soloPermissions->first()->permission);
    }

    /** @test */
    public function itCanHaveRolePermissions()
    {
        $this->assertEquals(2, $this->user->soloPermissions->count());
        $this->assertEquals('auth.permission.example', $this->user->soloPermissions->first()->permission);
    }

    /** @test */
    public function itCanGetAllPermissionsAcrossRolesAndSelf()
    {
        $this->assertCollectionSubset(collect([
            'auth.permission.example',
            'auth.permission.example2',
            'auth.permission.example4',
        ]), $this->user->permissions);
    }

    /** @test */
    public function itDoesntDisplayDuplicates()
    {
        $this->assertEquals(1, $this->user->permissions->filter(function ($key, $value) {
            return $value == 'auth.permission.example';
        })->count());
    }

    /** @test */
    public function itCanDetermineIfAUserHasAPermissionOrNot()
    {
        $this->assertTrue($this->user->hasPermission('auth.permission.example'));
        $this->assertTrue($this->user->hasPermission('auth.permission.example4'));
        $this->assertFalse($this->user->hasPermission('auth.permission.example5'));
    }
}
