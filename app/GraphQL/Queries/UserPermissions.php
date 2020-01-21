<?php

namespace App\GraphQL\Queries;

use App\User;

class UserPermissions
{
    public function directPermissions($rootValue, array $args)
    {
        return User::findOrFail($args['user_id'])->permissions()->pluck('permission');
    }

    public function rolePermissions($rootValue, array $args)
    {
        return User::findOrFail($args['user_id'])->getPermissionsViaRoles();
    }
}
