<?php

namespace App\Exceptions\Memberships;

use Exception;

class PrimaryMembershipDoesntAllowSecondaryException extends Exception
{
    protected $message = 'The current primary membership does not allow secondary memberships';
}
