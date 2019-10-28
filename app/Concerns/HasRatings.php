<?php


namespace App\Concerns;

use App\Libraries\CERT\VATSIMUserDetails;
use App\Models\Rating;
use App\Models\RatingPivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRatings
{
    public function ratings(): BelongsToMany
    {
        return $this->belongsToMany(
            Rating::class,
            'mship_account_qualification',
            'account_id',
            'qualification_id'
        )->using(RatingPivot::class)
            ->wherePivot('deleted_at', '=', null)
            ->withTimestamps();
    }

    public function syncRatings(int $actRatingCode, int $pilotRatingCode)
    {
        $ratings = collect();
        $ratings->push(Rating::find(5));
        // Handle ATC rating
        if ($actRatingCode === 0) {
        //   TODO:         $this->addNetworkBan('Network ban discovered via Cert login.');
        } elseif ($actRatingCode > 0) {
        //   TODO:         $this->removeNetworkBan();
            $ratings->push(Rating::atcRatingFromID($actRatingCode));
        }

        // Attempt to find non-instructor/admin rating
        if ($actRatingCode >= 8) {
            $previousRating = VATSIMUserDetails::getPreviousRatingsInfo($this->id);
            if ($previousRating && isset($previousRating->PreviousRatingInt) && $previousRating->PreviousRatingInt > 0) {
                $ratings->push(Rating::atcRatingFromID($previousRating->PreviousRatingInt));
            }
        }

        // Handle pilot ratings
        for ($i = 1; $i <= 256; $i *= 2) {
            if ($i & $pilotRatingCode) {
                $ratings->push(Rating::ofType('pilot')->networkValue($i)->first());
            }
        }

        $ratingIds = $ratings->pluck('id');
        if (!empty($ratingIds)) {
            $this->ratings()->syncWithoutDetaching($ratingIds);
            //TODO: Account Sync to external services
        }
    }

    public function getATCRatingAttribute()
    {
        return $this->ratings->filter(function ($rating) {
            return $rating->type == 'atc';
        })->sortByDesc(function ($rating, $key) {
            return $rating->pivot->created_at;
        })->first();
    }

    public function getPilotRatingsAttribute()
    {
        return $this->ratings->filter(function ($rating) {
            return $rating->type == 'pilot';
        });
    }
}
