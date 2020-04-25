<?php

namespace Tests\Feature\Api;

use App\Constants\BanTypeConstants;
use App\Models\Ban;
use App\Models\Membership;
use App\Models\Permissions\Assignment;
use App\Models\Rating;
use App\Models\Role;
use App\Passport\Client;
use App\User;
use Carbon\Carbon;
use Laravel\Passport\Passport;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class UserRetrievalTest extends TestCase
{
    use MakesGraphQLRequests;

    private $authUserQuery = '
        query{
            authUser{
                id
            }
        }
        ';

    public function testUnauthenticatedCantAccessMethods()
    {
        $this->assertApiUnauthenticatedResponse($this->graphQL($this->authUserQuery));

        $this->assertApiUnauthenticatedResponse($this->actingAs($this->user)->graphQL($this->authUserQuery));
    }

    public function testCanRetrieveAuthenticatedUser()
    {
        $this->asUserOnAPI();
        $this->graphQL($this->authUserQuery)->assertJson([
            'data' => [
                'authUser' => [
                    'id' => $this->user->id,
                ],
            ],
        ]);
    }

    public function testNonMachineTokenCantAccessOtherUsers()
    {
        $this->asUserOnAPI();
        $this->assertApiUnauthenticatedResponse($this->graphQL("
        query{
            user(id: {$this->user->id}){
                id
            }
        }
        "));
    }

    public function testCanRetrieveUserByID()
    {
        $this->asMachineMachine();
        $users = factory(User::class)->create();

        $this->graphQL("
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
        $this->asMachineMachine();
        $users = factory(User::class, 5)->create();
        $randomUsersId = $users->random()->id;

        $this->graphQL("
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
        $this->asMachineMachine();
        $ratings = factory(Rating::class, 2)->state('atc')->create();

        $this->user->ratings()->sync($ratings);

        $this->graphQL("
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
        $this->asMachineMachine();
        $rating = factory(Rating::class)->state('atc')->create();

        $this->user->ratings()->sync($rating);

        $this->graphQL("
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
        $this->asMachineMachine();
        $rating = factory(Rating::class)->state('pilot')->create();

        $this->graphQL("
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

        $this->graphQL("
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
        $this->asMachineMachine();
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

        $this->graphQL("
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
        $this->asMachineMachine();
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
        factory(Assignment::class)->state('user')->create([
            'related_id' => $this->user->id,
            'permission' => 'ukts.people.manage',
        ]);

        $this->graphQL("
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
        $this->asUserOnAPI();
        factory(Assignment::class)->state('user')->create([
            'related_id' => $this->user->id,
            'permission' => 'ukts.people.manage',
        ]);
        factory(Assignment::class)->state('user')->create([
            'related_id' => $this->user->id,
            'permission' => 'ukts.people.move',
        ]);

        $this->graphQL('
        query{
            authUserCan(permissions: ["ukts.people.manage"])
        }
        ')->assertJsonPath('data.authUserCan', true);

        $this->graphQL('
        query{
            authUserCan(permissions: ["ukts.people.manage", "ukts.people.move"])
        }
        ')->assertJsonPath('data.authUserCan', true);

        $this->graphQL('
        query{
            authUserCan(permissions: ["ukts.people.mutate"])
        }
        ')->assertJsonPath('data.authUserCan', false);
    }

    public function testItCanRetrieveMemberships()
    {
        $this->asUserOnAPI();

        // Add states
        $this->user->memberships()->attach(Membership::findByIdent(Membership::IDENT_DIVISION), [
            'ended_at' => Carbon::now()
        ]);
        $this->user->memberships()->attach(Membership::findByIdent(Membership::IDENT_INTERNATIONAL));
        $this->user->memberships()->attach(Membership::findByIdent(Membership::IDENT_VISITING));

        $result = $this->graphQL('
        query{
            authUser {
                memberships {
                    identifier
                }
                membershipHistory {
                    identifier
                }
                primaryMembership {
                    identifier
                }
                secondaryMemberships {
                    identifier
                }
            }
        }
        ');


        $result = $result->json('data.authUser');

        $this->assertCount(2, $result['memberships']);
        $this->assertCount(3, $result['membershipHistory']);
        $this->assertEquals(Membership::IDENT_INTERNATIONAL, $result['primaryMembership']['identifier']);
        $this->assertCount(1, $result['secondaryMemberships']);

    }

    private function asMachineMachine()
    {
        Passport::actingAsClient(new Client(), ['machine-only']);
    }

    private function asUserOnAPI()
    {
        Passport::actingAs($this->user);
    }

    private function assertApiUnauthenticatedResponse($response)
    {
        $response->assertJsonPath('errors.0.debugMessage', 'Unauthenticated.');
    }
}
