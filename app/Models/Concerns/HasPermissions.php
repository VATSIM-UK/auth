<?php

namespace App\Models\Concerns;

use App\Events\User\PermissionsChanged;
use App\Exceptions\InvalidPermissionException;
use App\Models\Permissions\Assignment;
use App\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use VATSIMUK\Support\Auth\Facades\PermissionValidity;

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
        return PermissionValidity::permissionSatisfiedByPermissions($permission, $this->getPermissionsViaRoles());
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
        return PermissionValidity::permissionSatisfiedByPermissions($permission, $this->permissions());
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
    public function givePermissionTo(...$permissions): self
    {
        $altered = false;

        collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }

                return $permission;
            })
            ->each(function ($permission) use (&$altered) {
                if (!PermissionValidity::isValidPermission($permission)) {
                    throw new InvalidPermissionException("The given permission, $permission, is not defined as a valid permission");
                }
                $altered = true;
                $this->permissions()->where('permission', $permission)->firstOrCreate([
                    'permission' => $permission,
                ]);
            });

        if ($this instanceof User && $altered) {
            event(new PermissionsChanged($this));
        }

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param string|array|Collection $permissions
     *
     * @return $this
     */
    public function syncPermissions(...$permissions): self
    {
        $this->permissions()->delete();

        if ($this instanceof User) {
            event(new PermissionsChanged($this));
        }

        return $this->givePermissionTo($permissions);
    }

    /**
     * Revoke the given permission.
     *
     * @param string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permission): self
    {
        $count = $this->permissions()->whereIn('permission', collect($permission))->delete();
        $this->load('permissions');

        if ($this instanceof User && $count > 0) {
            event(new PermissionsChanged($this));
        }

        return $this;
    }

    /**
     * Returns a collection of unique, local permissions assigned to this model.
     *
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return $this->permissions->pluck('permission')->unique();
    }
}
