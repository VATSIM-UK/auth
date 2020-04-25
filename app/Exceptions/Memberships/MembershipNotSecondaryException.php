<?php

namespace App\Exceptions\Memberships;

use Exception;

class MembershipNotSecondaryException extends Exception
{
    protected $message = "The subject membership is not a secondary membership";
}
