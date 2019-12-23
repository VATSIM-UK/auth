<?php

namespace App\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Assignment extends Model
{
    protected $table = "permission_assignments";
    protected $fillable = [
        'permission'
    ];
    public $timestamps = false;

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
