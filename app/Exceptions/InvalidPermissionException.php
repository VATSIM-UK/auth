<?php

namespace App\Exceptions;

class InvalidPermissionException extends PublicValidationException
{
    protected $message = 'The given permission was not valid';
}
