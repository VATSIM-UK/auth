<?php

namespace App\Models;

use App\Constants\RatingConstants;
use App\User;
use BenSampo\Enum\Traits\CastsEnums;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use CastsEnums;

    protected $connection = 'mysql';
    protected $table = 'ratings';
    public $timestamps = false;

    protected $enumCasts = [
        'type' => RatingConstants::class,
    ];

    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSpecialTypes($query)
    {
        return $query->whereIn('type', [RatingConstants::ADMIN, RatingConstants::TRAINING_ATC]);
    }

    public function scopeTypePilot($query)
    {
        return $query->ofType(RatingConstants::PILOT);
    }

    public function scopeTypeATC($query)
    {
        return $query->ofType(RatingConstants::ATC);
    }

    public function scopeNetworkValue($query, $networkValue)
    {
        return $query->where('vatsim_id', $networkValue);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_ratings', 'rating_id', 'user_id')
            ->using(RatingPivot::class)
            ->withTimestamps();
    }

    public static function atcRatingFromID(int $networkID)
    {
        if ($networkID < 1) {
            return;
        } elseif ($networkID >= 8 and $networkID <= 10) {
            $type = RatingConstants::TRAINING_ATC;
        } elseif ($networkID >= 11) {
            $type = RatingConstants::ADMIN;
        } else {
            $type = RatingConstants::ATC;
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
                $ro = self::ofType(RatingConstants::TYPE_PILOT)->networkValue($pow)->first();
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
