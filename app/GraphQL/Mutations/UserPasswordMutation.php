<?php

namespace App\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UserPasswordMutation
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
    public function updatePassword($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $user = Auth::guard('api')->user();

        if ($user->has_password) {
            if ($user->verifyPassword($args['old_password'])) {
                $user->setPassword($args['new_password']);

                return true;
            }

            throw ValidationException::withMessages([
                'old_password' => ['Incorrect previous password given'],
            ]);
        }

        $user->setPassword($args['new_password']);

        return true;
    }

    public function removePassword($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $user = Auth::guard('api')->user();

        if (! $user->has_password) {
            return false;
        }

        if (! $user->verifyPassword($args['current_password'])) {
            throw ValidationException::withMessages([
                'current_password' => ['Incorrect current password given'],
            ]);
        }

        $user->removePassword();

        return true;
    }
}
