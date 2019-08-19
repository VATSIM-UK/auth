<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $connection = 'mysql_core';
    protected $table = 'mship_account';

    protected $hidden = [
        'password', 'remember_token',
    ];
}
