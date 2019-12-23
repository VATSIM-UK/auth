<?php


namespace App\Models\Concerns;


use App\Models\Permissions\Assignment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait CanHavePermissions
{
    public function permissions(): MorphMany
    {
        return $this->morphMany(Assignment::class, 'related');
    }

    public function addPermission($permission)
    {

    }

    public function takePermission($permission)
    {

    }


    public function hasPermission($permission): bool
    {
        return $this->permissions()->where('permission', $permission)->exists();
    }
}
