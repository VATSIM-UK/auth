<?php

namespace Tests\Unit;

use App\Events\User\Banned;
use App\Events\User\BanRepealed;
use App\Exceptions\Ban\AlreadyNetworkBannedException;
use App\Exceptions\Ban\BanEndsBeforeStartException;
use App\Models\Ban;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserBansTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function itCanHaveNoBans()
    {
        $this->assertEmpty($this->user->bans);
    }

    /** @test */
    public function itCanHaveNoCurrentBans()
    {
        $this->assertEmpty($this->user->currentBans);

        factory(Ban::class)->create([
            'user_id' => $this->user->id,
            'starts_at' => Carbon::now()->subDays(2),
            'ends_at' => Carbon::now()->subDays(1),
        ]);

        $this->assertEmpty($this->user->fresh()->currentBans);
        $this->assertFalse($this->user->banned);
    }

    /** @test */
    public function itCanHaveBan()
    {
        $this->assertEmpty($this->user->currentBans);
        $this->assertFalse($this->user->banned);

        factory(Ban::class)->create([
            'user_id' => $this->user->id,
        ]);
        $this->assertNotEmpty($this->user->fresh()->currentBans);
        $this->assertTrue($this->user->banned);
    }

    /** @test */
    public function itCanHaveIndeterminateBan()
    {
        $this->assertEmpty($this->user->currentBans);
        $this->assertFalse($this->user->banned);

        factory(Ban::class)->create([
            'user_id' => $this->user->id,
            'ends_at' => null,
        ]);

        $this->assertNotEmpty($this->user->fresh()->currentBans);
        $this->assertTrue($this->user->banned);
    }

    /** @test */
    public function itCanGetBanReason()
    {
        $reason = factory(Ban\Reason::class)->create([
            'name' => 'Silly Billy',
        ]);

        factory(Ban::class)->create([
            'user_id' => $this->user->id,
            'reason_id' => $reason,
        ]);

        $this->assertEquals($reason->id, $this->user->currentBans->first()->reason->id);
        $this->assertEquals($reason->name, $this->user->currentBans->first()->reason->name);
    }

    /** @test */
    public function itCanBanLocally()
    {
        $this->assertEmpty($this->user->currentBans);

        // Pause time
        Carbon::setTestNow(Carbon::now());

        $reason = factory(Ban\Reason::class)->create([
            'period' => 'T12H', // 12 hours
        ]);

        $banner = factory(User::class)->create();

        $this->user->banLocally('Did something bad', $reason, $banner);

        Event::assertDispatched(Banned::class);
        $this->assertEquals(Carbon::now()->addHours(12), $this->user->fresh()->currentBans->first()->ends_at);
    }

    /** @test */
    public function itCanBanLocallyWithExplicitDuration()
    {
        $this->assertEmpty($this->user->currentBans);

        // Pause time
        Carbon::setTestNow(Carbon::now());

        $banner = factory(User::class)->create();

        $this->user->banLocally('Did something bad', null, $banner, Carbon::now()->addMonth());

        Event::assertDispatched(Banned::class);
        $this->assertEquals(Carbon::now()->addMonth(), $this->user->fresh()->currentBans->first()->ends_at);
    }

    /** @test */
    public function itCanBanLocallyWithBannerID()
    {
        $this->assertEmpty($this->user->currentBans);

        // Pause time
        Carbon::setTestNow(Carbon::now());

        $reason = factory(Ban\Reason::class)->create([
            'period' => '1DT12H', // 12 hours
        ]);

        $banner = factory(User::class)->create();

        $this->user->banLocally('Did something bad', $reason, $banner->id);

        Event::assertDispatched(Banned::class);
        $this->assertEquals(Carbon::now()->addDay()->addHours(12), $this->user->fresh()->currentBans->first()->ends_at);
    }

    /** @test */
    public function itCanNetworkBan()
    {
        $this->assertEmpty($this->user->currentBans);
        $this->assertNull($this->user->networkBan);
        $this->user->banNetwork();

        Event::assertDispatched(Banned::class);
        $this->assertEquals('Network Ban Discovered', $this->user->fresh()->currentBans->first()->body);
        $this->assertNotNull($this->user->networkBan);
    }

    /** @test */
    public function itCantNetworkBanTwice()
    {
        $this->user->banNetwork();

        $this->expectException(AlreadyNetworkBannedException::class);

        $this->user->banNetwork();

        $this->assertEquals(1, $this->user->bans);
    }

    /** @test */
    public function itCanHaveNetworkBanEnded()
    {
        $this->user->banNetwork();

        $this->assertTrue($this->user->banned);

        $this->user->endNetworkBanIfHas();

        $this->assertFalse($this->user->banned);
    }

    /** @test */
    public function itCanHaveBanRepealed()
    {
        factory(Ban::class)->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($this->user->banned);

        $this->user->currentBans()->first()->repeal();

        $this->assertFalse($this->user->fresh()->banned);
        Event::assertDispatched(BanRepealed::class);
    }

    /** @test */
    public function itCantHaveABanEndBeforeStart()
    {
        $this->expectException(BanEndsBeforeStartException::class);

        $this->user->banLocally('Did something bad', null, null, Carbon::now()->subDay());
    }
}
