<?php

namespace App\GraphQL\Mutations;

use App\Models\Role;

class RoleMutation
{
    public function create($rootValue, array $args): bool
    {
        $role = Role::create($args);
        $role->syncPermissions($args['permissions']);

        return true;
    }

    public function update($rootValue, array $args): bool
    {
        $role = Role::findOrFail($args['id']);

        if (! $args['require_password']) {
            $args['password_refresh_rate'] = null;
        }

        $role->fill($args);
        $role->save();

        $role->syncPermissions($args['permissions']);

        return true;
    }
}
