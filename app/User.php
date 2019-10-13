<?php

namespace App;

use App\Concerns\HasPassword;
use App\Models\Rating;
use App\Models\RatingPivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasPassword;

    protected $connection = 'mysql_core';
    protected $table = 'mship_account';

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

    public function ratings(): BelongsToMany
    {
        return $this->belongsToMany(
            Rating::class,
            'mship_account_qualification',
            'account_id',
            'qualification_id'
        )->using(RatingPivot::class)
            ->wherePivot('deleted_at', '=', null)
            ->withTimestamps();
    }

    public function getATCRatingAttribute()
    {
        return $this->ratings->filter(function ($rating) {
            return $rating->type == 'atc';
        })->sortByDesc(function ($rating, $key) {
            return $rating->pivot->created_at;
        })->first();
    }

    public function getPilotRatingsAttribute()
    {
        return $this->ratings->filter(function ($rating) {
            return $rating->type == 'pilot';
        });
    }
}
