<?php

namespace App\GraphQL\Mutations;

use App\Models\Role;
use App\User;

class UserRoleMutations
{
    public function giveRole($rootValue, array $args): bool
    {
        $user = User::findOrFail($args['user_id']);
        $role = Role::findOrFail($args['role_id']);

        $user->assignRole($role);

        return true;
    }

    public function syncRoles($rootValue, array $args): bool
    {
        $user = User::findOrFail($args['user_id']);
        $user->syncRoles($args['role_ids']);

        return true;
    }

    public function takeRole($rootValue, array $args): bool
    {
        $user = User::findOrFail($args['user_id']);
        $role = Role::findOrFail($args['role_id']);

        $user->removeRole($role);

        return true;
    }
}
