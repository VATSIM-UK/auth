<?php

namespace App\Models;

use App\Models\Concerns\HasPermissions;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/*
 * Note: the 'password_refresh_rate' attribute is in days
 */

class Role extends Model
{
    use HasPermissions;

    protected $fillable = [
        'name',
        'require_password',
        'password_refresh_rate',
    ];

    public function scopeForcesPassword($query)
    {
        return $query->where('require_password', true);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public static function findByName(string $name): self
    {
        return self::where('name', $name)->firstOrFail();
    }

    public function hasPermissionTo($permission): bool
    {
        return $this->hasDirectPermission($permission);
    }
}
