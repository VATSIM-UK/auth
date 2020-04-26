<?php

namespace App\GraphQL\Mutations;

use App\Models\Membership;
use App\User;

class UserMembershipMutation
{
    public function addVisitingMembership($rootValue, array $args): bool
    {
        $user = User::findOrFail($args['user_id']);
        $membership = Membership::findByIdent(Membership::IDENT_VISITING);

        $user->addSecondaryMembership($membership);

        return true;
    }

    public function addTransferringMembership($rootValue, array $args): bool
    {
        $user = User::findOrFail($args['user_id']);
        $membership = Membership::findByIdent(Membership::IDENT_TRANSFERING);

        $user->addSecondaryMembership($membership);

        return true;
    }
}
