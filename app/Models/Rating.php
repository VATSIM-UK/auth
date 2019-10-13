<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $connection = 'mysql_core';
    protected $table = 'mship_qualification';

    public $timestamps = false;


}
