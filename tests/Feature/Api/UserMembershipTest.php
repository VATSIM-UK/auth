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
        ")->assertJsonStructure(["errors"]);
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
        ")->assertJsonStructure(["errors"]);
    }
}
