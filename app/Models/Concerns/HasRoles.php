<?php

namespace App\Models\Concerns;

use App\Events\User\RolesChanged;
use App\Models\Role;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    use HasPermissions;

    public static function bootHasRoles()
    {
        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param Builder $query
     * @param string|array|Collection $roles
     * @return Builder
     */
    public function scopeRole(Builder $query, $roles): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $roles = array_map(function ($role) {
            if ($role instanceof Role) {
                return $role;
            }
            $method = is_numeric($role) ? 'find' : 'findByName';

            return Role::{$method}($role);
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhere('roles.id', $role->id);
                }
            });
        });
    }

    /**
     * Assign the given role to the model.
     *
     * @param Collection|array|string ...$roles
     *
     * @return $this
     */
    public function assignRole(...$roles): self
    {
        $roles = $this->roleInputToIds($roles)->all();

        $changes = $this->roles()->sync($roles, false);

        // If the effected Model is a user, count the number of rows updated to see if we should fire an event.
        // The sync above will return an array of the changes like such:
        //        [
        //            'attached' => [1, 2, 3],
        //            'detached' => [4, 5],
        //            'updated' => [6]
        //        ]

        if ($this instanceof User && collect($changes)->sum(function ($value) {
                return count($value);
            }) > 0) {
            event(new RolesChanged($this));
        }

        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param string|Role $role
     *
     * @return self
     */
    public function removeRole($role): self
    {
        $count = $this->roles()->detach($this->getStoredRole($role));
        $this->load('roles');

        if ($this instanceof User && $count > 0) {
            event(new RolesChanged($this));
        }

        return $this;
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param array|Role|string ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles): self
    {
        $roles = $this->roleInputToIds($roles);

        $changes = $this->roles()->sync($roles);

        if ($this->fresh()->requiresPassword() && !$this->hasPassword()) {
            // Invalidate User's Session, forcing them through Auth to set a secondary password
            $this->setRememberToken(null);
            $this->save();
        }

        if ($this instanceof User && collect($changes)->sum(function ($value) {
                return count($value);
            }) > 0) {
            event(new RolesChanged($this));
        }

        return $this;
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param string|int|array|Role $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }
        if (is_numeric($roles) && $roles = (int)$roles) {
            return $this->hasRoleByID($roles);
        }
        if (is_string($roles)) {
            return $this->hasRoleByName($roles);
        }
        if ($roles instanceof Role) {
            return $this->hasRoleByID($roles->id);
        }
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns if the model has a role by a role ID
     *
     * @param int $roleID
     * @return bool
     */
    public function hasRoleByID(int $roleID): bool
    {
        return $this->roles->contains('id', $roleID);
    }

    /**
     * Returns if the model has a role by a role name
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRoleByName(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * @param string|array|Role|Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param string|Role|Collection|array $roles
     * @return bool
     */
    public function hasAllRoles($roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }
        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }
        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->id : $role;
        });

        return $roles->intersect($this->roles()->pluck('id')) == $roles;
    }

    /**
     * Get the names of the roles assigned to the model.
     *
     * @return Collection
     */
    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    /**
     * Return a role model from an id or name.
     *
     * @param int|string|Role $role
     * @return Role
     * @throws ModelNotFoundException
     */
    protected function getStoredRole($role): Role
    {
        if (is_numeric($role)) {
            return Role::findOrFail($role);
        }
        if (is_string($role)) {
            return Role::findByName($role);
        }

        return $role;
    }

    /**
     * Converts a glued string into an array of parts.
     *
     * @param string $pipeString
     * @return array
     */
    protected function convertPipeToArray(string $pipeString): array
    {
        $pipeString = trim($pipeString);
        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }
        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);
        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }
        if (!in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }

    /**
     * Converts an input list of role identifiers, and returns a collection of their id's.
     *
     * @param array|Role|string $roles
     * @return Collection
     */
    private function roleInputToIds($roles): Collection
    {
        return collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->map->id;
    }
}
