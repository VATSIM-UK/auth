<?php

namespace App\GraphQL\Mutations;

use App\User;

class UserPermissionMutations
{
    public function givePermission($rootValue, array $args): bool
    {
        $user = User::findOrFail($args['user_id']);
        $user->givePermissionTo($args['permission']);

        return true;
    }

    public function syncPermissions($rootValue, array $args): bool
    {
        $user = User::findOrFail($args['user_id']);
        $user->syncPermissions($args['permissions']);

        return true;
    }

    public function takePermission($rootValue, array $args): bool
    {
        $user = User::findOrFail($args['user_id']);
        $user->revokePermissionTo($args['permission']);

        return true;
    }
}
