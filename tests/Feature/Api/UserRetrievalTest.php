<?php

namespace Tests\Feature\Api;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class UserRetrievalTest extends TestCase
{
    use DatabaseTransactions, MakesGraphQLRequests;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Laravel\Passport\Http\Middleware\CheckClientCredentials::class);
    }

    public function testUnauthenticatedCantAccessMethods()
    {
        $this->graphQL('
        query{
            authUser{
                id
            }
        }
        ')->assertJsonFragment([
                "debugMessage" => "Unauthenticated."
        ]);


        $this->actingAs($this->user)->graphQL('
        query{
            authUser{
                id
            }
        }
        ')->assertJsonFragment([
                "debugMessage" => "Unauthenticated."
        ]);
    }

    public function testCanRetrieveAuthenticatedUser()
    {
        $this->actingAs($this->user, 'api')->graphQL('
        query{
            authUser{
                id
            }
        }
        ')->assertJson([
            "data" => [
                "authUser" => [
                    "id" => $this->user->id
                ]
            ]
        ]);
    }

    public function testCanRetrieveUserByID()
    {
        $users = factory(User::class)->create();

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            user(id: {$this->user->id}){
                id
            }
        }
        ")->assertJson([
            "data" => [
                "user" => ["id" => "{$this->user->id}"]
            ]
        ]);
    }

    public function testCanRetrieveUsersByIDs()
    {
        $users = factory(User::class, 5)->create();
        $randomUsersId = $users->random()->id;

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            users(ids: [{$this->user->id},{$randomUsersId}]){
                id
            }
        }
        ")->assertJsonFragment([
            "data" => [
                "users" => [
                    ["id" => "{$this->user->id}"],
                    ["id" => "$randomUsersId"],
                ]
            ]
        ]);
    }
}
