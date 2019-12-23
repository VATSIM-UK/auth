<?php

namespace App\Passport;

class Client extends \Laravel\Passport\Client
{
    public function skipsAuthorization()
    {
        return $this->user_id ? false : true;
    }
}
