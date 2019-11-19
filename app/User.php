<?php

namespace App;

use App\Concerns\HasBans;
use App\Concerns\HasPassword;
use App\Concerns\HasRatings;
use App\Models\Rating;
use App\Models\RatingPivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasPassword, HasRatings, HasBans;

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
        'inactive' => 'bool'
    ];

    public function getHasPasswordAttribute(): bool
    {
        return (bool) $this->password;
    }
}
