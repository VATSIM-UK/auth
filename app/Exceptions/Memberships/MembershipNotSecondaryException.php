<?php

namespace App\Exceptions\Memberships;

use App\Exceptions\PublicValidationException;

class MembershipNotSecondaryException extends PublicValidationException
{
    protected $message = 'The subject membership is not a secondary membership';
}
