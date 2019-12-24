<?php


namespace Tests\Unit\Services;


use App\Models\Permissions\Assignment;
use App\Services\PermissionValidityService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PermissionValidityServiceTest extends TestCase
{
    use DatabaseTransactions;

    /* @var PermissionValidityService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(PermissionValidityService::class, function ($mock) {
            $mock->shouldReceive('loadJsonPermissions')
                ->andReturn([
                    "auth" => [
                        "permissions" => [
                            "assign",
                            "view"
                        ],
                        "users" => [
                            "create",
                            "update",
                            "delete",
                            "modify" => [
                                "name",
                                "age"
                            ]
                        ]
                    ]
                ]);
        })->makePartial();

        $this->service = resolve(PermissionValidityService::class);
    }

    /** @test */
    public function itCanLoadPermissionsFile()
    {
        $service = new PermissionValidityService();
        $this->assertFalse($service->isValidPermission('test.permission.that.doesnt.exist'));
    }

    /** @test */
    public function itIdentifiesIfPermissionIsValid()
    {
        $this->assertTrue($this->service->isValidPermission('auth.users.create'));
        $this->assertTrue($this->service->isValidPermission('auth.users.modify.age'));
        $this->assertTrue($this->service->isValidPermission('auth.permissions.*'));
        $this->assertTrue($this->service->isValidPermission('auth.users.modify.*'));
        $this->assertFalse($this->service->isValidPermission('auth.permissions*'));
        $this->assertFalse($this->service->isValidPermission('auth.users.mutate'));
        $this->assertFalse($this->service->isValidPermission('auth.users'));
        $this->assertFalse($this->service->isValidPermission('example.doesnt.exist'));
    }

    /** @test */
    public function itReportsIfPermissionIsGrantedFromListOfHeldPermissions()
    {
        $assignment = factory(Assignment::class)->create([
            'permission' => 'auth.permissions.view'
        ]);
        factory(Assignment::class)->create([
            'related_id' => $assignment->related_id,
            'permission' => 'auth.users.*'
        ]);
        $this->user->givePermissionTo(['auth.permissions.view', 'auth.users.*']);

        $validPermissions = [
            'auth.permissions.view',
            'auth.users.*'
        ];

        $invalidPermissions = [
            'auth.permissions.view',
            'auth.users.edit'
        ];
        // Array Input
        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.users.create', $validPermissions));
        $this->assertFalse($this->service->permissionSatisfiedByPermissions('auth.users.create', $invalidPermissions));

        // Collection Input
        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.users.create', collect($validPermissions)));
        $this->assertFalse($this->service->permissionSatisfiedByPermissions('auth.users.create', collect($invalidPermissions)));

        // MorphsMany Permissions Relationship
        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.users.create', $this->user->permissions()));
        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.users.create', $assignment->related->permissions()));

        $this->assertFalse($this->service->permissionSatisfiedByPermissions('auth.users.create', []));
    }

    /** @test */
    public function itCanDetermineIfPermissionFulfilledByWildcard()
    {
        $permissions = [
            "auth.user.*",
            "auth.permission.modify.*"
        ];

        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.users', $permissions));
        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.users.create', $permissions));
        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.users.create.destroy', $permissions));

        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.permission.modify', $permissions));
        $this->assertTrue($this->service->permissionSatisfiedByPermissions('auth.permission.modify.alter', $permissions));
        $this->assertFalse($this->service->permissionSatisfiedByPermissions('auth.permission.create', $permissions));

        $this->assertTrue($this->service->permissionSatisfiedByPermissions('can.do.anything', ['*']));
    }
}
