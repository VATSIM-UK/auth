<?php


namespace App\Models\Concerns;


use App\Models\Permissions\Assignment;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasPermissionsAndRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function soloPermissions(): MorphMany
    {
        return $this->morphMany(Assignment::class, 'related');
    }

    public function rolePermissions(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions->pluck('permission');
            })->sort()->values();
    }

    public function hasPermission($permission): bool
    {
        return $this->soloPermissions()->where('permission', $permission)->exists() || $this->rolePermissions()->search($permission) !== false;
    }

    public function getPermissionsAttribute(): Collection
    {
        return $this->rolePermissions()->merge($this->soloPermissions()->pluck('permission'));
    }
}
