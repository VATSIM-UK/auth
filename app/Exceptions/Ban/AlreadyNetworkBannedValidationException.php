<?php

namespace App\Exceptions\Ban;

use App\Exceptions\PublicValidationException;

class AlreadyNetworkBannedValidationException extends PublicValidationException
{
    protected $message = 'This user is already network banned';
}
