<?php

namespace App\GraphQL\Queries;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AuthorizeUser
{
    /**
     * Checks if the authenticated user is able to perform a permission or not.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     * @throws AuthorizationException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /* @var User */
        $user = Auth::user();
        if (! $user) {
            throw new AuthorizationException();
        }

        return $user->hasAllPermissions($args['permissions']);
    }
}
