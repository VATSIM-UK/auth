<?php


namespace App\Services;


use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PermissionValidityService
{

    private $jsonPermissions;

    /**
     * Determines whether the permission exists as defined by the permissions files
     *
     * @param string $permission
     * @return bool
     */
    public function isValidPermission($permission): bool
    {
        // 1: Remove Wildcards
        $permission = str_replace('.*', '', $permission, $count);
        $permissionSplit = collect(explode('.', $permission));

        // 2: Load permissions file and parse
        $permissions = $this->loadJsonPermissions();

        // 3: Check if the file has the permission
        return (is_array(data_get($permissions, $permission)) && $count > 0) // If it is a wildcard, the result should be an array
            || collect(data_get($permissions, str_replace('.' . $permissionSplit->last(), '', $permission)))
                ->filter(function ($item) {
                    return !is_array($item);
                })
                ->search($permissionSplit->last()) !== false;
    }

    /**
     * @param $permission
     * @param Collection|MorphMany|array $permissions
     * @return bool
     */
    public function permissionSatisfiedByPermissions($permission, $permissions): bool
    {
        if (is_array($permissions)) {
            $permissions = collect($permissions);
        }

        // 1: Check for exact match
        if ($permissions instanceof MorphMany ?
            (clone $permissions)->where('permission', $permission)->exists() : $permissions->search($permission) !== false) {
            return true;
        }

        $wildcardPermissions = $permissions instanceof MorphMany ? $permissions->where('permission', 'like', '%*%')->pluck('permission') : $permissions->filter(function ($value) {
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

    /**
     * Loads the JSON permissions
     *
     * @return array
     */
    public function loadJsonPermissions(): array
    {
        return $this->jsonPermissions ? $this->jsonPermissions : $this->jsonPermissions = json_decode(file_get_contents(resource_path('permissions/permissions.json')), true);
    }
}
