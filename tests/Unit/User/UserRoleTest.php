<?php

namespace Tests\Unit;

use App\Models\Permissions\Assignment;
use App\Models\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use DatabaseTransactions;

    /* @var Role */
    private $role1;
    private $role2;
    private $role3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role1 = factory(Role::class)->create(['name' => 'Super Admin']);
        $this->role2 = factory(Role::class)->create(['name' => 'Member']);
        $this->role3 = factory(Role::class)->create(['name' => 'Non-Member']);

        factory(Assignment::class)->create(['related_id' => $this->role1, 'permission' => 'auth.permission.example']);
        factory(Assignment::class)->create(['related_id' => $this->role2, 'permission' => 'auth.permission.example2']);
        factory(Assignment::class)->create(['related_id' => $this->role3, 'permission' => 'auth.permission.example2']);
        factory(Assignment::class)->create(['related_id' => $this->role3, 'permission' => 'auth.permission.example3']);

        $this->user->roles()->sync([$this->role1->id, $this->role2->id]);
        factory(Assignment::class, 'user')->create(['related_id' => $this->user->id, 'permission' => 'auth.permission.example4']);
        factory(Assignment::class, 'user')->create(['related_id' => $this->user->id, 'permission' => 'auth.permission.example']);
        factory(Assignment::class, 'user')->create(['related_id' => $this->user->id + 10, 'permission' => 'auth.permission.example5']);
        $this->user = $this->user->fresh();
    }

    /** @test */
    public function itDeletesRolesWhenDeleted()
    {
        $this->user->delete();

        $this->assertDatabaseMissing('user_roles', [
            'role_id' => $this->role1->id,
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseMissing('user_roles', [
            'role_id' => $this->role2->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function itCanHaveRoles()
    {
        $this->assertEquals(2, $this->user->roles->count());
        $this->assertCollectionSubset(collect(['Super Admin', 'Member']), $this->user->roles->pluck('name'));
    }

    /** @test */
    public function itCanTellIfItHasRoles()
    {
        $this->assertTrue($this->user->hasRole($this->role1->name));
        $this->assertTrue($this->user->hasRole($this->role1->id));
        $this->assertTrue($this->user->hasRole($this->role1));
        $this->assertTrue($this->user->hasRole([$this->role1]));
        $this->assertTrue($this->user->hasRole([$this->role1, $this->role2->id]));

        $this->assertFalse($this->user->hasRole($this->role3));
        $this->assertFalse($this->user->hasRole([factory(Role::class)->create(), $this->role3]));
    }

    /** @test */
    public function itCanFilterByRole()
    {
        $user2 = factory(User::class)->create();
        $user2->roles()->attach($this->role2);
        $user2->roles()->attach($this->role3);

        $this->assertEquals($this->user->id, User::role($this->role1)->pluck('id')->implode(' '));
        $this->assertEquals($this->user->id, User::role(collect([$this->role1]))->pluck('id')->implode(' '));
        $this->assertEquals(collect([$this->user->id, $user2->id])->sort()->values(), User::role($this->role2->id)->pluck('id')->sort()->values());
        $this->assertEquals($user2->id, User::role([$this->role3])->pluck('id')->implode(' '));
    }

    /** @test */
    public function itCanAssignRoles()
    {
        $newRoles = factory(Role::class, 3)->create();

        $this->user->assignRole([$newRoles->first(), null]);
        $this->assertTrue($this->user->hasRole($newRoles->first()));

        $this->user->assignRole($newRoles->take(-2));
        $this->assertTrue($this->user->fresh()->hasRole($newRoles->get(2)));
        $this->assertTrue($this->user->fresh()->hasRole($newRoles->get(1)->id . '|' . $newRoles->get(2)->id));
        $this->assertTrue($this->user->fresh()->hasRole($newRoles->last()));
    }

    /** @test */
    public function itCanRemoveRoles()
    {
        $this->user->removeRole($this->role2);
        $this->assertFalse($this->user->hasRole($this->role2));

        $this->user->removeRole($this->role1->id);
        $this->assertFalse($this->user->hasRole($this->role1));
    }

    /** @test */
    public function itCanSyncRoles()
    {
        $newRoles = factory(Role::class, 2)->create();
        $this->user->syncRoles($newRoles);

        $this->assertEquals($newRoles->pluck('id')->sort()->values(), $this->user->roles()->pluck('id')->sort()->values());
    }

    /** @test */
    public function itCanReportIfItHasAnyOfTheGivenRoles()
    {
        $newRoles = factory(Role::class, 2)->create();
        $this->user->assignRole($newRoles->first());

        $this->assertTrue($this->user->hasAnyRole($newRoles));
        $this->assertTrue($this->user->hasAnyRole([$this->role1->id, $newRoles->last()->id]));
        $this->assertFalse($this->user->hasAnyRole([$this->role3, $newRoles->last()]));
    }

    /** @test */
    public function itCanReportIfItHasAllOfTheGivenRoles()
    {
        $newRoles = factory(Role::class, 2)->create();
        $this->user->assignRole($newRoles->first());

        $this->assertFalse($this->user->hasAllRoles($newRoles));
        $this->assertTrue($this->user->hasAllRoles($newRoles->first()));
        $this->assertTrue($this->user->hasAllRoles($newRoles->first()->name));
        $this->assertFalse($this->user->hasAllRoles('roles|that|dont|exist'));
        $this->assertTrue($this->user->hasAllRoles([$this->role1->id, $newRoles->first()->id]));
        $this->assertFalse($this->user->hasAllRoles([$this->role3, $newRoles->last()]));
    }

    /** @test */
    public function itCanGetRoleNames()
    {
        $this->role1->update(['name' => 'Role 1']);
        $this->role2->update(['name' => 'Role 2']);

        $this->assertEquals(collect(['Role 1', 'Role 2']), $this->user->getRoleNames()->sort()->values());
    }
}
