<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $connection = 'mysql_core';
    protected $table = 'mship_qualification';

    public $timestamps = false;

    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeOfType($query, $type)
    {
        return $query->whereType($type);
    }

    public function scopeNetworkValue($query, $networkValue)
    {
        return $query->whereVatsim($networkValue);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'mship_account_qualification', 'qualification_id', 'account_id')
            ->using(RatingPivot::class)
            ->wherePivot('deleted_at', '=', null)
            ->withTimestamps();
    }

    public static function atcRatingFromID(int $networkID)
    {
        if ($networkID < 1) {
            return;
        } elseif ($networkID >= 8 and $networkID <= 10) {
            $type = 'training_atc';
        } elseif ($networkID >= 11) {
            $type = 'admin';
        } else {
            $type = 'atc';
        }

        // Sort out the atc ratings
        return self::ofType($type)->networkValue($networkID)->first();
    }

    public static function pilotRatingFromID(int $networkID)
    {
        $ratingsOutput = [];
        // Let's check each bitmask....
        for ($i = 0; $i <= 8; $i++) {
            $pow = pow(2, $i);
            if (($pow & $networkID) == $pow) {
                $ro = self::ofType('pilot')->networkValue($pow)->first();
                if ($ro) {
                    $ratingsOutput[] = $ro;
                }
            }
        }
        return $ratingsOutput;
    }

    public function __toString()
    {
        return $this->code;
    }

    public function getIsOBSAttribute()
    {
        return $this->code == 'OBS';
    }

    public function getIsS1Attribute()
    {
        return $this->code == 'S1';
    }

    public function getIsS2Attribute()
    {
        return $this->code == 'S2';
    }

    public function getIsS3Attribute()
    {
        return $this->code == 'S3';
    }

    public function getIsC1Attribute()
    {
        return $this->code == 'C1';
    }

    public function getIsC3Attribute()
    {
        return $this->code == 'C3';
    }
}
