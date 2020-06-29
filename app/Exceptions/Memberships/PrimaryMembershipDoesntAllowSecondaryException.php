<?php

namespace App\Exceptions\Memberships;

use App\Exceptions\PublicValidationException;

class PrimaryMembershipDoesntAllowSecondaryException extends PublicValidationException
{
    protected $message = 'The current primary membership does not allow secondary memberships';
}
