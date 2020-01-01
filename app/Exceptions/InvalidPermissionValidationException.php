<?php

namespace App\Exceptions;

class InvalidPermissionValidationException extends PublicValidationException
{
    protected $message = "The given permission was not valid";
}
