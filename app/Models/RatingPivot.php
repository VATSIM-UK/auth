<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RatingPivot extends Pivot
{
    protected $table = 'user_ratings';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['id'];
    public $incrementing = true;

}
