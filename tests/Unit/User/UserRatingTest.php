<?php

namespace Tests\Unit;

use App\Models\Rating;
use App\Models\RatingPivot;
use Carbon\Carbon;
use Tests\TestCase;

class UserRatingTest extends TestCase
{
    /** @test */
    public function itCantHaveNoRatings()
    {
        $this->assertEquals('OBS', $this->user->atcRating->code);
        $this->assertTrue($this->user->pilotRatings->isEmpty());
    }

    /** @test */
    public function itCanHaveMultiplePilotRatings()
    {
        $ratings = factory(Rating::class, 3)->state('pilot')->create();
        $this->assertTrue($this->user->pilotRatings->isEmpty());
        $this->user->ratings()->sync([$ratings->first()->id, $ratings->last()->id]);

        $this->assertEquals(2, count($this->user->fresh()->pilotRatings));
        $this->assertEquals([$ratings->first()->id, $ratings->last()->id], $this->user->fresh()->pilotRatings->pluck('id')->all());
    }

    /** @test */
    public function itReturnsLatestATCRating()
    {
        $firstRating = Rating::typeATC()->networkValue('4')->first();
        $secondRating = Rating::typeATC()->networkValue('5')->first();

        $this->user->syncRatings($firstRating->vatsim_id, 0);
        $this->assertEquals($firstRating->id, $this->user->fresh()->atcRating->id);

        $this->user->syncRatings($secondRating->vatsim_id, 0);
        $this->assertEquals($secondRating->id, $this->user->fresh()->atcRating->id);
    }

    /** @test */
    public function itReturnsCurrentSpecialTypes()
    {
        $firstRating = Rating::typeATC()->networkValue('4')->first(); // S3
        $secondRating = Rating::networkValue('8')->first(); // I1
        $thirdRating = Rating::networkValue('10')->first(); // I3

        $this->user->ratings()->sync([$firstRating->id, $secondRating->id, $thirdRating->id]);
        RatingPivot::where('user_id', $this->user->id)->where('rating_id', $secondRating->id)->update([
            'deleted_at' => Carbon::now(),
        ]);

        $this->assertEquals($thirdRating->id, $this->user->specialRating->id);
    }

    /** @test */
    public function itCorrectlyUpdatesSpecialRatings()
    {
        $firstRating = Rating::typeATC()->networkValue('4')->first(); // S3
        $secondRating = Rating::networkValue('8')->first(); // I1
        $thirdRating = Rating::networkValue('10')->first(); // I3

        $this->user->ratings()->sync([$firstRating->id, $secondRating->id]);

        $this->assertEquals($firstRating->id, $this->user->atcRating->id);
        $this->assertEquals($secondRating->id, $this->user->specialRating->id);

        Carbon::setTestNow(Carbon::now());
        $this->user->syncRatings($thirdRating->vatsim_id, 0);

        $this->assertEquals($firstRating->id, $this->user->atcRating->id);
        $this->assertEquals($thirdRating->id, $this->user->fresh()->specialRating->id);
        $this->assertDatabaseHas('user_ratings', [
            'user_id' => $this->user->id,
            'rating_id' => $secondRating->id,
            'deleted_at' => Carbon::now(),
        ]);

        // Go from having special rating to not having special rating

        $this->user->syncRatings($firstRating->vatsim_id, 0);

        $this->assertEquals($firstRating->id, $this->user->atcRating->id);
        $this->assertNull($this->user->fresh()->specialRating);
        $this->assertDatabaseHas('user_ratings', [
            'user_id' => $this->user->id,
            'rating_id' => $thirdRating->id,
            'deleted_at' => Carbon::now(),
        ]);
    }
}
