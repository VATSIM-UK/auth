<?php

namespace Tests\Feature\Api;

use App\Models\Rating;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class UserRetrievalTest extends TestCase
{
    use DatabaseTransactions, MakesGraphQLRequests;

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

    public function testCanRetrieveUsersRatings()
    {
        $ratings = factory(Rating::class, 2)->create();

        $this->user->ratings()->sync($ratings);

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            user(id: {$this->user->id}){
                ratings {
                    code
                }
            }
        }
        ")->assertJsonFragment([
            "data" => [
                "user" => [
                    "ratings" => [
                        ['code' => $ratings->first()->code],
                        ['code' => $ratings->last()->code],
                    ]
                ]
            ]
        ]);
    }

    public function testCanRetrieveUsersATCRating()
    {
        $rating = factory(Rating::class, 'atc')->create();

        $this->user->ratings()->sync($rating);

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            user(id: {$this->user->id}){
                atcRating {
                    code
                }
            }
        }
        ")->assertJsonFragment([
            "data" => [
                "user" => [
                    "atcRating" => ['code' => $rating->code],
                ]
            ]
        ]);
    }

    public function testCanRetrieveUsersPilotRatings()
    {
        $rating = factory(Rating::class, 'pilot')->create();

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            user(id: {$this->user->id}){
                pilotRatings {
                    code
                }
            }
        }
        ")->assertJsonFragment([
            "data" => [
                "user" => [
                    "pilotRatings" => [],
                ]
            ]
        ]);


        $this->user->ratings()->sync($rating);

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            user(id: {$this->user->id}){
                pilotRatings {
                    code
                }
            }
        }
        ")->assertJsonFragment([
            "data" => [
                "user" => [
                    "pilotRatings" => [['code' => $rating->code]],
                ]
            ]
        ]);
    }
}
