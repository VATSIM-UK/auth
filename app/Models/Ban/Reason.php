<?php

namespace App\Models\Ban;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $table = "ban_reasons";

    function getPeriodIntervalAttribute(): CarbonInterval
    {
        /*
         * The input for CarbonInterval follows the ISO_8601 duration format (https://en.wikipedia.org/wiki/ISO_8601#Durations)
         *
         * For our purposes, the P is being appended automatically. Thus the `period` column should be like the following formats:
         *  "12D" (12 Days)
         *  "1DT12H" (1 Day, 2 Hours - note the T designates the end of the date component and start of the time components)
         */
        return CarbonInterval::create('P' . $this->period);
    }
}
