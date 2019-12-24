<?php

namespace App\Models\Concerns;

use App\Models\Permissions\Assignment;
use App\Services\PermissionValidityService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasPermissions
{
    public static function bootHasPermissions()
    {
        static::deleting(function ($model) {
            $model->permissions()->delete();
        });
    }

    public function permissions(): MorphMany
    {
        return $this->morphMany(Assignment::class, 'related');
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     */
    public function hasAnyPermission(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has all of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     */
    public function hasAllPermissions(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }
        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the model has, via roles, the given permission.
     *
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermissionViaRole($permission): bool
    {
        return resolve(PermissionValidityService::class)->permissionSatisfiedByPermissions($permission, $this->getPermissionsViaRoles());
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string $permission
     *
     * @return bool
     */
    public function hasDirectPermission($permission): bool
    {
        return resolve(PermissionValidityService::class)->permissionSatisfiedByPermissions($permission, $this->permissions());
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getPermissionsViaRoles(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions->pluck('permission');
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getAllPermissions(): Collection
    {
        $permissions = $this->getPermissions();
        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->unique()->sort()->values();
    }

    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|Collection $permissions
     *
     * @return $this
     */
    public function givePermissionTo(...$permissions)
    {
        $validityService = resolve(PermissionValidityService::class);
        collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }

                return $permission;
            })
            ->each(function ($permission) use ($validityService) {
                if (!$validityService->isValidPermission($permission)) {
                    return;
                }

                $this->permissions()->where('permission', $permission)->firstOrCreate([
                    'permission' => $permission,
                ]);
            });

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param string|array|Collection $permissions
     *
     * @return $this
     */
    public function syncPermissions(...$permissions)
    {
        $this->permissions()->delete();

        return $this->givePermissionTo($permissions);
    }

    /**
     * Revoke the given permission.
     *
     * @param string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permission)
    {
        $this->permissions()->whereIn('permission', collect($permission))->delete();
        $this->load('permissions');

        return $this;
    }

    public function getPermissions(): Collection
    {
        return $this->permissions->pluck('permission')->unique();
    }
}
