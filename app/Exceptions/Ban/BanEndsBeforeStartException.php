<?php

namespace App\Exceptions\Ban;

use Exception;

class BanEndsBeforeStartException extends Exception
{
    protected $message = 'The selected end date for the ban is before or equal to the start date!';
}
