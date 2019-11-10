<?php

namespace App\Models\Ban;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $table = "ban_reasons";

    function getPeriodIntervalAttribute(): CarbonInterval
    {
        return CarbonInterval::create('P' . $this->period);
    }
}
