<?php

namespace Tests\Feature\Api;

use App\Exceptions\Memberships\PrimaryMembershipDoesntAllowSecondaryException;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class UserMembershipTest extends TestCase
{
    use MakesGraphQLRequests;

    public function testCanGiveVisitingMembershipToUser()
    {
        $this->asMachineMachine();

        $this->graphQL("
            mutation {
                addVisitingMembershipToUser(user_id: {$this->user->id})
            }
        ")->assertJsonPath('data.addVisitingMembershipToUser', true);

        $this->user->updatePrimaryMembership('GBR', 'EUR');

        $this->graphQL("
            mutation {
                addVisitingMembershipToUser(user_id: {$this->user->id})
            }
        ")->assertJsonPath('errors.0.message', (new PrimaryMembershipDoesntAllowSecondaryException)->getMessage());
    }

    public function testCanGiveTransferringMembershipToUser()
    {
        $this->asMachineMachine();

        $this->graphQL("
            mutation {
                addTransferringMembershipToUser(user_id: {$this->user->id})
            }
        ")->assertJsonPath('data.addTransferringMembershipToUser', true);

        $this->user->updatePrimaryMembership('GBR', 'EUR');

        $this->graphQL("
            mutation {
                addTransferringMembershipToUser(user_id: {$this->user->id})
            }
        ")->assertJsonPath('errors.0.message', (new PrimaryMembershipDoesntAllowSecondaryException)->getMessage());
    }
}
