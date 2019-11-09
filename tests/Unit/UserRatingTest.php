<?php


namespace Tests\Unit;


use App\Models\Rating;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserRatingTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCantHaveNoRatings(){
       $this->assertEquals('OBS', $this->user->atcRating->code);
       $this->assertEquals(true, $this->user->pilotRatings->isEmpty());
    }

    /** @test */
    public function itCanHaveMultiplePilotRatings(){

        $ratings = factory(Rating::class, 'pilot', 3)->create();
        $this->assertEquals(true, $this->user->pilotRatings->isEmpty());
        $this->user->ratings()->sync([$ratings->first()->id, $ratings->last()->id]);

        $this->assertEquals(2, count($this->user->fresh()->pilotRatings));
        $this->assertEquals([$ratings->first()->id, $ratings->last()->id], $this->user->fresh()->pilotRatings->pluck('id')->all());
    }

    /** @test */
    public function itReturnsLatestATCRating(){
        $firstRating = Rating::typeATC()->networkValue('4')->first();
        $secondRating = Rating::typeATC()->networkValue('5')->first();

        $this->user->syncRatings($firstRating->vatsim_id, 0);
        $this->assertEquals($firstRating->id, $this->user->fresh()->atcRating->id);

        $this->user->syncRatings($secondRating->vatsim_id, 0);
        $this->assertEquals($secondRating->id, $this->user->fresh()->atcRating->id);
    }
}
