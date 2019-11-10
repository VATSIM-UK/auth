<?php


namespace App\Concerns;

use App\Events\User\Updated;
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
            "user_ratings",
            'user_id',
            'rating_id'
        )->using(RatingPivot::class)
            ->withTimestamps();
    }

    public function syncRatings(int $actRatingCode, int $pilotRatingCode)
    {
        $ratings = collect();

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
                $ratings->push(Rating::typePilot()->networkValue($i)->first());
            }
        }

        $ratingIds = $ratings->pluck('id')->filter(function ($value) {
            return $value != null;
        });

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
}
