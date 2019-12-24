<?php

namespace App\Models\Concerns;

use App\Events\User\Updated;
use App\Libraries\CERT\VATSIMUserDetails;
use App\Models\Rating;
use App\Models\RatingPivot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRatings
{
    public function ratings(): BelongsToMany
    {
        return $this->belongsToMany(
            Rating::class,
            'user_ratings',
            'user_id',
            'rating_id'
        )->using(RatingPivot::class)
            ->wherePivot('deleted_at', '=', null)
            ->withPivot('deleted_at')
            ->withTimestamps();
    }

    public function syncRatings(int $atcRatingCode, int $pilotRatingCode): void
    {
        $ratings = collect();

        // Handle ATC rating
        if ($atcRatingCode === 0) {
            $this->banNetwork('Network ban discovered via Cert login.');

            return;
        }

        // Check if has non-ATC primary rating
        if ($atcRatingCode >= 8) {
            $previousRating = VATSIMUserDetails::getPreviousRatingsInfo($this->id);
            if ($previousRating && isset($previousRating->PreviousRatingInt) && $previousRating->PreviousRatingInt > 0) {
                // Push pre-special rating
                $ratings->push(Rating::atcRatingFromID($previousRating->PreviousRatingInt));
            }

            // Check if we are going to be adding a different "special" rating than current
            if ($this->specialRating->id != $atcRatingCode) {
                $pivot = $this->specialRating->pivot;
                $pivot->deleted_at = Carbon::now();
                $pivot->save();
            }
        } else {

            // Remove any "special" ATC ratings
            if ($this->specialRating) {
                $pivot = $this->specialRating->pivot;
                $pivot->deleted_at = Carbon::now();
                $pivot->save();
            }
        }

        $this->endNetworkBanIfHas();
        // Push current rating
        $ratings->push(Rating::atcRatingFromID($atcRatingCode));

        // Handle pilot ratings
        for ($i = 1; $i <= 256; $i *= 2) {
            if ($i & $pilotRatingCode) {
                $ratings->push(Rating::typePilot()->networkValue($i)->first());
            }
        }
        $ratingIds = $ratings->pluck('id');

        $currentRatingIds = $this->ratings()->distinct()->pluck('ratings.id');

        if (!empty($idsToSync = $ratingIds->diff($currentRatingIds))) {
            $this->ratings()->syncWithoutDetaching($idsToSync);
            event(new Updated($this));
        }
    }

    public function getATCRatingAttribute()
    {
        $rating = $this->ratings()->typeATC()->get()
            ->sortByDesc(function ($rating, $key) {
                return $rating->id;
            })
            ->sortByDesc(function ($rating, $key) {
                return $rating->pivot->created_at;
            })->first();

        return $rating ? $rating : Rating::code('OBS')->first();
    }

    public function getPilotRatingsAttribute()
    {
        return $this->ratings()->typePilot()->get();
    }

    public function getSpecialRatingAttribute()
    {
        return $this->ratings()->specialTypes()->first();
    }
}
