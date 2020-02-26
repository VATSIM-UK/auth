<?php

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Exceptions\ValidationException;

class UserPasswordMutation
{
    public function updatePassword($rootValue, array $args): bool
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

    public function removePassword($rootValue, array $args): bool
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
