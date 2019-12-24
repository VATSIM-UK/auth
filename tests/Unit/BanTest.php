<?php

namespace Tests\Unit;

use App\Constants\BanTypeConstants;
use App\Models\Ban;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BanTest extends TestCase
{
    use DatabaseTransactions;

    /* @var Ban */
    private $ban1, $ban2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ban1 = factory(Ban::class)->create([
            'type' => BanTypeConstants::LOCAL
        ]);
        $this->ban2 = factory(Ban::class)->create([
            'type' => BanTypeConstants::NETWORK,
            'repealed_at' => Carbon::now()
        ]);
    }

    /** @test */
    public function itCanGetLocalScopedBans()
    {
        $this->assertEquals([$this->ban1->id], Ban::local()->pluck('id')->all());
    }

    /** @test */
    public function itCanGetNetworkScopedBans()
    {
        $this->assertEquals([$this->ban2->id], Ban::network()->pluck('id')->all());
    }

    /** @test */
    public function itCanGetRepealedBans()
    {
        $this->assertEquals([$this->ban2->id], Ban::repealed()->pluck('id')->all());
    }

    /** @test */
    public function itCanGetNotRepealedBans()
    {
        $this->assertEquals([$this->ban1->id], Ban::notRepealed()->pluck('id')->all());
    }

    /** @test */
    public function itCanBeEndedEarly()
    {
        $this->ban1->ends_at = null;
        $this->assertTrue($this->ban1->end());
        $this->assertFalse($this->ban1->end()); // false if already ended
    }

    /** @test */
    public function itReportsItsLocality()
    {
        $this->assertTrue($this->ban1->is_local);
        $this->assertTrue($this->ban2->is_network);
        $this->assertFalse($this->ban2->is_local);
        $this->assertFalse($this->ban1->is_network);
    }
}
