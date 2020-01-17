<?php

namespace App\GraphQL\Mutations;

use App\Models\Role;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UserRoleMutations
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return bool
     */
    public function giveRole($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $user = User::findOrFail($args['user_id']);
        $role = Role::findOrFail($args['role_id']);

        $user->assignRole($role);

        return true;
    }

    public function syncRoles($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $user = User::findOrFail($args['user_id']);
        $user->syncRoles($args['role_ids']);

        return true;
    }

    public function takeRole($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $user = User::findOrFail($args['user_id']);
        $role = Role::findOrFail($args['role_id']);

        $user->removeRole($role);

        return true;
    }
}
