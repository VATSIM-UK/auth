<?php

namespace App;

use App\Models\Concerns\HasBans;
use App\Models\Concerns\HasMemberships;
use App\Models\Concerns\HasPassword;
use App\Models\Concerns\HasRatings;
use App\Models\Concerns\HasRoles;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordInterface;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements CanResetPasswordInterface
{
    use Notifiable, HasApiTokens, HasPassword, HasRatings, HasBans, CanResetPassword, HasRoles, HasMemberships;

    protected $table = 'users';

    public $incrementing = false;

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
        'inactive' => 'bool',
        'id' => 'int'
    ];

    protected $dates = [
        'password_set_at',
    ];

    public function getNameFullAttribute()
    {
        return $this->name_first.' '.$this->name_last;
    }

    public function getHasPasswordAttribute(): bool
    {
        return (bool) $this->password;
    }

    public function getAllPermissionsAttribute(): Collection
    {
        return $this->getAllPermissions();
    }
}
