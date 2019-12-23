<?php

namespace App\Models\Concerns;


use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasPermissionsAndRoles
{
    use CanHavePermissions;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function rolePermissions(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions->pluck('permission');
            })->sort()->values();
    }

    public function hasPermissionTo($permission): bool
    {
        // 1: Check for exact match
        if ($this->permissions()->where('permission', $permission)->exists() || $this->rolePermissions()->search($permission) !== false) {
            return true;
        }

        $wildcardPermissions = $this->permissions->filter(function ($value) {
            return Str::contains($value, '*');
        });

        // 2: Check for wildcard
        if ($wildcardPermissions->isEmpty()) {
            return false;
        }

        // 3: Have some wildcard permissions. Check if they match the required permission
        return $wildcardPermissions->search(function ($value) use ($permission) {
                return fnmatch($value, $permission) || fnmatch(str_replace('.*', '*', $value), $permission);
            }) !== false;
    }

    public function getPermissionsAttribute(): Collection
    {
        return $this->rolePermissions()->merge($this->permissions()->pluck('permission'));
    }
}
