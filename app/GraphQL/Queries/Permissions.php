<?php

namespace App\GraphQL\Queries;

use VATSIMUK\Support\Auth\Facades\PermissionValidity;

class Permissions
{
    public function __invoke()
    {
        return PermissionValidity::loadJsonPermissions();
    }
}
