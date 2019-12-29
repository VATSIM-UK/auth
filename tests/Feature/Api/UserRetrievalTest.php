<?php

namespace Tests\Feature\Api;

use App\Constants\BanTypeConstants;
use App\Models\Ban;
use App\Models\Permissions\Assignment;
use App\Models\Rating;
use App\Models\Role;
use App\User;
use Carbon\Carbon;
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
                'debugMessage' => 'Unauthenticated.',
        ]);

        $this->actingAs($this->user)->graphQL('
        query{
            authUser{
                id
            }
        }
        ')->assertJsonFragment([
                'debugMessage' => 'Unauthenticated.',
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
            'data' => [
                'authUser' => [
                    'id' => $this->user->id,
                ],
            ],
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
            'data' => [
                'user' => ['id' => "{$this->user->id}"],
            ],
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
            'data' => [
                'users' => [
                    ['id' => "{$this->user->id}"],
                    ['id' => "$randomUsersId"],
                ],
            ],
        ]);
    }

    public function testCanRetrieveUsersRatings()
    {
        $ratings = factory(Rating::class, 'atc', 2)->create();

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
            'data' => [
                'user' => [
                    'ratings' => [
                        ['code' => $ratings->first()->code],
                        ['code' => $ratings->last()->code],
                    ],
                ],
            ],
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
                    type
                }
            }
        }
        ")->assertJsonFragment([
            'data' => [
                'user' => [
                    'atcRating' => [
                        'code' => $rating->code,
                        'type' => 'ATC',
                    ],
                ],
            ],
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
            'data' => [
                'user' => [
                    'pilotRatings' => [],
                ],
            ],
        ]);

        $this->user->ratings()->sync($rating);

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            user(id: {$this->user->id}){
                pilotRatings {
                    code
                    type
                }
            }
        }
        ")->assertJsonFragment([
            'data' => [
                'user' => [
                    'pilotRatings' => [
                        [
                            'code' => $rating->code,
                            'type' => 'PILOT',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testCanRetrieveUsersBans()
    {
        factory(Ban::class)->create([
            'type' => BanTypeConstants::LOCAL,
            'user_id' => $this->user->id,
            'starts_at' => Carbon::now()->subDays(2),
            'ends_at' => Carbon::now()->subDays(1),
        ]);
        factory(Ban::class)->create([
            'type' => BanTypeConstants::NETWORK,
            'user_id' => $this->user->id,
            'ends_at' => null,
        ]);

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            user(id: {$this->user->id}){
                banned
                bans {
                    starts_at
                    ends_at
                }
                network_ban {
                    ends_at
                }
                currentBans {
                    type
                    starts_at
                    ends_at
                }
            }
        }
        ")->assertJsonCount(2, 'data.user.bans')
            ->assertJsonCount(1, 'data.user.currentBans')
            ->assertJsonPath('data.network_ban.ends_at', null)
            ->assertJsonPath('data.user.banned', true);
    }

    public function testCanRetrieveUsersRolesAndPermissions()
    {
        $roles = factory(Role::class, 2)->create();
        $this->user->syncRoles($roles->pluck('id')->all());
        factory(Assignment::class)->create([
            'related_id' => $roles->first()->id,
            'permission' => 'ukts.users.manage',
        ]);
        factory(Assignment::class)->create([
            'related_id' => $roles->last()->id,
            'permission' => 'ukts.emails.manage',
        ]);
        factory(Assignment::class, 'user')->create([
            'related_id' => $this->user->id,
            'permission' => 'ukts.people.manage',
        ]);

        $this->actingAs($this->user, 'api')->graphQL("
        query{
            user(id: {$this->user->id}){
                roles {
                    name
                }
                all_permissions
            }
        }
        ")->assertJsonFragment([
            'roles' => [
                ['name' => $roles->first()->name],
                ['name' => $roles->last()->name],
            ],
        ])->assertJsonFragment([
            'all_permissions' => [
                'ukts.users.manage',
                'ukts.emails.manage',
                'ukts.people.manage',
            ],
        ]);
    }

    public function testCanCheckIfUserAuthorisedForPermission()
    {
        factory(Assignment::class, 'user')->create([
            'related_id' => $this->user->id,
            'permission' => 'ukts.people.manage',
        ]);
        factory(Assignment::class, 'user')->create([
            'related_id' => $this->user->id,
            'permission' => 'ukts.people.move',
        ]);

        $this->actingAs($this->user, 'api')->graphQL('
        query{
            authUserCan(permissions: ["ukts.people.manage"])
        }
        ')->assertJsonPath('data.authUserCan', true);

        $this->actingAs($this->user, 'api')->graphQL('
        query{
            authUserCan(permissions: ["ukts.people.manage", "ukts.people.move"])
        }
        ')->assertJsonPath('data.authUserCan', true);

        $this->actingAs($this->user, 'api')->graphQL('
        query{
            authUserCan(permissions: ["ukts.people.mutate"])
        }
        ')->assertJsonPath('data.authUserCan', false);
    }
}
