<?php

namespace Tests\Feature\Api;

use App\Models\Permissions\Assignment;
use App\Models\Role;
use App\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;
use VATSIMUK\Support\Auth\Facades\PermissionValidity;

class PermissionsAndRolesTest extends TestCase
{
    use MakesGraphQLRequests;

    private $role;
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->withoutPermissions();
        $this->actingAs($this->user, 'api');

        PermissionValidity::partialMock()
            ->shouldReceive('isValidPermission')
            ->andReturn(true)->byDefault();

        $this->subject = factory(User::class)->create();

        // Give the user a direct permission
        $this->subject->givePermissionTo('auth.direct.permission');

        // Create a role with a permission
        $assignment = factory(Assignment::class)->create([
            'permission' => 'auth.role.permission',
        ]);
        $this->subject->assignRole($assignment->related);

        $this->role = factory(Role::class)->create([
            'name' => 'My Role',
        ]);
    }

    public function testItCanIndexRoles()
    {
        $this->graphQL("
            query {
                role(id: {$this->role->id}) {
                    name
                }
                roles {
                    name
                }
                userRoles(user_id: {$this->subject->id}){
                    name
                }
            }
        ")->assertJsonPath('data.role.name', 'My Role')
            ->assertJsonCount(2, 'data.roles')
            ->assertJsonCount(1, 'data.userRoles');
    }

    public function testItCanIndexPermissions()
    {
        $permissions = [
            'auth' => [
                'permissions' => [
                    'assign',
                    'view',
                ],
            ],
        ];
        PermissionValidity::partialMock()
            ->shouldReceive('loadJsonPermissions')
            ->andReturn($permissions);

        $this->graphQL("
            query {
                permissions
                userRolePermissions(user_id: {$this->subject->id})
                userDirectPermissions(user_id: {$this->subject->id})
            }
        ")->assertJsonPath('data.permissions', $permissions)
            ->assertJsonPath('data.userRolePermissions', ['auth.role.permission'])
            ->assertJsonPath('data.userDirectPermissions', ['auth.direct.permission']);
    }

    public function testItCanGiveAndRemoveARole()
    {
        $this->assertFalse($this->subject->hasRole($this->role));
        $this->graphQL("
            mutation {
                giveRoleToUser(user_id: {$this->subject->id}, role_id: {$this->role->id})
            }
        ")->assertJsonPath('data.giveRoleToUser', true);

        $this->assertTrue($this->subject->fresh()->hasRole($this->role));

        $this->graphQL("
            mutation {
                takeRoleFromUser(user_id: {$this->subject->id}, role_id: {$this->role->id})
            }
        ")->assertJsonPath('data.takeRoleFromUser', true);

        $this->assertFalse($this->subject->fresh()->hasRole($this->role));
    }

    public function testItCanSyncRoles()
    {
        $existingRole = $this->subject->roles->first();
        $this->assertFalse($this->subject->hasRole($this->role));
        $this->assertTrue($this->subject->hasRole($existingRole));

        $this->graphQL("
            mutation {
                syncRolesWithUser(user_id: {$this->subject->id}, role_ids: [{$this->role->id}])
            }
        ")->assertJsonPath('data.syncRolesWithUser', true);

        $this->assertTrue($this->subject->fresh()->hasRole($this->role));
        $this->assertFalse($this->subject->fresh()->hasRole($existingRole));
    }

    public function testItCanGiveAndRemoveAPermission()
    {
        $this->assertFalse($this->subject->hasDirectPermission('auth.given.permission'));
        $this->graphQL("
            mutation {
                givePermissionToUser(user_id: {$this->subject->id}, permission: \"auth.given.permission\")
            }
        ")->assertJsonPath('data.givePermissionToUser', true);

        $this->assertTrue($this->subject->hasDirectPermission('auth.given.permission'));

        $this->graphQL("
            mutation {
                takePermissionFromUser(user_id: {$this->subject->id}, permission: \"auth.given.permission\")
            }
        ")->assertJsonPath('data.takePermissionFromUser', true);

        $this->assertFalse($this->subject->hasDirectPermission('auth.given.permission'));
    }

    public function testItCanSyncPermissions()
    {
        $this->assertFalse($this->subject->hasDirectPermission('auth.given.permission'));
        $this->assertTrue($this->subject->hasDirectPermission('auth.direct.permission'));

        $this->graphQL("
            mutation {
                syncPermissionsWithUser(user_id: {$this->subject->id}, permissions: [\"auth.given.permission\"])
            }
        ")->assertJsonPath('data.syncPermissionsWithUser', true);

        $this->assertTrue($this->subject->hasDirectPermission('auth.given.permission'));
        $this->assertFalse($this->subject->hasDirectPermission('auth.direct.permission'));
    }

    public function testItFailsWhenPermissionIsNotValid()
    {
        PermissionValidity::partialMock()
            ->shouldReceive('isValidPermission')
            ->andReturn(false);

        $this->graphQL("
            mutation {
                givePermissionToUser(user_id: {$this->subject->id}, permission: \"auth.invalid.permission\")
            }
        ")->assertJsonPath('errors.0.extensions.validation.0', 'The given permission, auth.invalid.permission, is not defined as a valid permission');
    }

    public function testItCanCreateUpdateAndDeleteRole()
    {
        PermissionValidity::partialMock()
            ->shouldReceive('isValidPermission')
            ->andReturn(true);

        $roleID = $this->graphQL('
            mutation {
                createRole(name: "My Second Role", require_password: false, permissions: ["role.permission"]){
                    id
                }
            }
        ')->json('data.createRole.id');

        $this->assertDatabaseHas('roles', [
            'id' => $roleID,
            'name' => 'My Second Role',
            'require_password' => false,
            'password_refresh_rate' => null,
        ]);

        $this->assertDatabaseHas('permission_assignments', [
            'related_type' => Role::class,
            'permission' => 'role.permission',
        ]);

        $this->graphQL("
            mutation {
                editRole(id: $roleID, name: \"My Updated Role\", require_password: false, permissions: [\"role.permission.next\"]){
                    id
                }
            }
        ");

        $this->assertDatabaseMissing('roles', [
            'name' => 'My Second Role',
            'require_password' => false,
            'password_refresh_rate' => null,
        ]);

        $this->assertDatabaseMissing('permission_assignments', [
            'related_type' => Role::class,
            'permission' => 'role.permission',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'My Updated Role',
            'require_password' => false,
            'password_refresh_rate' => null,
        ]);

        $this->assertDatabaseHas('permission_assignments', [
            'related_type' => Role::class,
            'permission' => 'role.permission.next',
        ]);

        $this->graphQL("
            mutation {
                deleteRole(id: $roleID){
                    id
                }
            }
        ");

        $this->assertDatabaseMissing('roles', [
            'id' => $roleID,
        ]);
    }
}
