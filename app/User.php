<?php

namespace App;

use App\Models\Concerns\HasRoles;
use App\Concerns\HasBans;
use App\Concerns\HasPassword;
use App\Concerns\HasRatings;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordInterface;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements CanResetPasswordInterface
{
    use Notifiable, HasApiTokens, HasPassword, HasRatings, HasBans, CanResetPassword, HasRoles;

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
    ];

    public function getHasPasswordAttribute(): bool
    {
        return (bool) $this->password;
    }

    public function getAllPermissionsAttribute(): Collection
    {
        return $this->getAllPermissions();
    }
}
