<?php

namespace App\Exceptions\Ban;

use Exception;

class AlreadyNetworkBannedException extends Exception
{
    protected $message = 'This user is already network banned';
}
