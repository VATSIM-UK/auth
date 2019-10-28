<?php


namespace Tests\Unit;


use App\Models\Rating;
use Carbon\Carbon;
use Tests\TestCase;

class UserRatingTest extends TestCase
{
    /** @test */
    public function itCantHaveNoRatings(){
        if(Rating::code('OBS')->count() == 0){
            factory(Rating::class, 'atc')->create([
                'code' => 'OBS'
            ]);
        }

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
        if(!$firstRating = Rating::ofType('atc')->networkValue('4')->first()){
            $firstRating = factory(Rating::class, 'atc')->create([
                'vatsim' => 4,
                'code' => 'S3'
            ]);
        }
        if(!$secondRating = Rating::ofType('atc')->networkValue('5')->first()){
            $secondRating = factory(Rating::class, 'atc')->create([
                'vatsim' => 5,
                'code' => 'C1'
            ]);
        }
        $this->user->syncRatings($firstRating->vatsim, 0);
        $this->assertEquals($firstRating->id, $this->user->fresh()->atcRating->id);

        $this->user->syncRatings($secondRating->vatsim, 0);
        $this->assertEquals($secondRating->id, $this->user->fresh()->atcRating->id);
    }
}
