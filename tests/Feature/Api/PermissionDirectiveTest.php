<?php

namespace Tests\Feature\Api;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PermissionDirectiveTest extends TestCase
{
    use MakesGraphQLRequests;

    private $permissionProtectedQuery = ("
            query {
                roles {
                    name
                }
            }
        ");
    private $permission = 'auth.roles';
    private $queryMethodName = 'roles';

    public function testUnauthorizedCantAccessQuery()
    {
        $this->graphQL($this->permissionProtectedQuery)
            ->assertJsonPath('errors.0.debugMessage', 'Unauthenticated.');

        $this->actingAs($this->user, 'api')
            ->graphQL($this->permissionProtectedQuery)
            ->assertJsonPath('errors.0.message', "You are not authorized to perform action '{$this->queryMethodName}'");
    }

    public function testAuthorizedCantAccessQuery()
    {
        $this->user->givePermissionTo($this->permission);
        $this->actingAs($this->user, 'api')
            ->graphQL($this->permissionProtectedQuery)
            ->assertJsonMissing([
                "errors"
            ]);
    }
}
