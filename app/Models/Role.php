<?php

namespace App\Models;

use App\Models\Permissions\Assignment;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Role extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function permissions(): MorphMany
    {
        return $this->morphMany(Assignment::class, 'related');
    }
}
