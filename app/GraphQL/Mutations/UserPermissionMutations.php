<?php

namespace App\GraphQL\Mutations;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UserPermissionMutations
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
    public function givePermission($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $user = User::findOrFail($args['user_id']);
        $user->givePermissionTo($args['permission']);

        return true;
    }

    public function syncPermissions($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $user = User::findOrFail($args['user_id']);
        $user->syncPermissions($args['permissions']);

        return true;
    }

    public function takePermission($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $user = User::findOrFail($args['user_id']);
        $user->revokePermissionTo($args['permission']);

        return true;
    }
}
