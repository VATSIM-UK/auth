<?php

namespace App;

use App\Concerns\HasPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasPassword;

    protected $connection = 'mysql_core';
    protected $table = 'mship_account';

    protected $fillable = [
        'id',
        'name_first',
        'name_last',
        'email',
        'password',
        'password_set_at',
        'password_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'inactive' => 'bool'
    ];

    /**
     * Determine whether the current account has a password set.
     *
     * @return bool
     */
    public function hasPassword()
    {
        return $this->password !== null;
    }
}
