<?php

namespace App\Models\Concerns;

use App\Events\User\RolesChanged;
use App\Models\Role;
use App\User;
use Illuminate\Database\Eloquent\Builder;
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
     * @param string $guard
     *
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
    public function assignRole(...$roles)
    {
        $roles = $this->roleInputToIds($roles)->all();

        $model = $this->getModel();

        $changes = $this->roles()->sync($roles, false);
        $model->load('roles');


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
    public function removeRole($role)
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
    public function syncRoles(...$roles)
    {
        $roles = $this->roleInputToIds($roles);
        $changes = $this->roles()->sync($roles);

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
            return $this->roles->contains('id', $roles);
        }
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }
        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
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

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

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

    protected function convertPipeToArray(string $pipeString)
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
