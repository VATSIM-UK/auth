<?php

namespace App\Models;

use App\Constants\RatingTypeConstants;
use App\User;
use BenSampo\Enum\Traits\CastsEnums;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rating extends Model
{
    use CastsEnums;

    protected $table = 'ratings';
    public $timestamps = false;

    protected $casts = [
        'type' => 'int',
    ];

    protected $enumCasts = [
        'type' => RatingTypeConstants::class,
    ];

    public function scopeCode($query, $code): Builder
    {
        return $query->where('code', $code);
    }

    public function scopeOfType($query, $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeSpecialTypes($query): Builder
    {
        return $query->whereIn('type', [RatingTypeConstants::ADMIN, RatingTypeConstants::TRAINING_ATC]);
    }

    public function scopeTypePilot($query): Builder
    {
        return $query->ofType(RatingTypeConstants::PILOT);
    }

    public function scopeTypeATC($query): Builder
    {
        return $query->ofType(RatingTypeConstants::ATC);
    }

    public function scopeNetworkValue($query, $networkValue): Builder
    {
        return $query->where('vatsim_id', $networkValue);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_ratings', 'rating_id', 'user_id')
            ->using(RatingPivot::class)
            ->withTimestamps();
    }

    public static function atcRatingFromID(int $networkID): ?self
    {
        if ($networkID < 1) {
            return null;
        } elseif ($networkID >= 8 and $networkID <= 10) {
            $type = RatingTypeConstants::TRAINING_ATC;
        } elseif ($networkID >= 11) {
            $type = RatingTypeConstants::ADMIN;
        } else {
            $type = RatingTypeConstants::ATC;
        }

        // Sort out the atc ratings
        return self::ofType($type)->networkValue($networkID)->first();
    }

    public static function pilotRatingFromID(int $networkID): self
    {
        $ratingsOutput = [];
        // Let's check each bitmask....
        for ($i = 0; $i <= 8; $i++) {
            $pow = pow(2, $i);
            if (($pow & $networkID) == $pow) {
                $ro = self::ofType(RatingTypeConstants::TYPE_PILOT)->networkValue($pow)->first();
                if ($ro) {
                    $ratingsOutput[] = $ro;
                }
            }
        }

        return $ratingsOutput;
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function getIsOBSAttribute(): bool
    {
        return $this->code == 'OBS';
    }

    public function getIsS1Attribute(): bool
    {
        return $this->code == 'S1';
    }

    public function getIsS2Attribute(): bool
    {
        return $this->code == 'S2';
    }

    public function getIsS3Attribute(): bool
    {
        return $this->code == 'S3';
    }

    public function getIsC1Attribute(): bool
    {
        return $this->code == 'C1';
    }

    public function getIsC3Attribute(): bool
    {
        return $this->code == 'C3';
    }
}
