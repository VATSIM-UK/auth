<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingPivot extends Pivot
{
    use SoftDeletes;

    protected $table = 'mship_account_qualification';
    protected $connection = 'mysql_core';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['id'];
    public $incrementing = true;
}
