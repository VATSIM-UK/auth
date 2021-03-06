<?php

namespace App\Exceptions\Ban;

use App\Exceptions\PublicValidationException;

class BanEndsBeforeStartException extends PublicValidationException
{
    protected $message = 'The selected end date for the ban is before or equal to the start date!';
}
