<?php

namespace App\GraphQL\Queries;

use App\User;

class UserPermissions
{
    public function directPermissions($rootValue, array $args)
    {
        return User::with(['permissions'])->findOrFail($args['user_id'])->pluck('permission');
    }

    public function rolePermissions($rootValue, array $args)
    {
        return User::with(['roles'])->findOrFail($args['user_id'])->getPermissionsViaRoles();
    }
}
