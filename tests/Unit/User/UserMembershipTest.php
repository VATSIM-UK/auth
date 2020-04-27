<?php

namespace Tests\Unit\User;

use App\Exceptions\Memberships\MembershipNotSecondaryException;
use App\Exceptions\Memberships\PrimaryMembershipDoesntAllowSecondaryException;
use App\Models\Membership;
use App\User;
use Carbon\Carbon;
use Mockery\MockInterface;
use Tests\TestCase;

class UserMembershipTest extends TestCase
{
    private $activeMembership;
    private $pastMembership;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activeMembership = factory(Membership::class)->create();
        $this->pastMembership = factory(Membership::class)->create();

        $this->user->memberships()->attach($this->activeMembership->id, [
            'started_at' => Carbon::now()->subYear(),
        ]);

        $this->user->memberships()->attach($this->pastMembership->id, [
            'started_at' => Carbon::now()->subYears(2),
            'ended_at' => Carbon::now()->subYear(),
        ]);
    }

    /** @test */
    public function itCanHaveActiveAndPastMemberships()
    {
        $this->assertEquals(1, $this->user->memberships()->count());
        $this->assertEquals(2, $this->user->membershipHistory()->count());
    }

    /** @test */
    public function itCanHaveAPrimaryMembership()
    {
        $this->assertEquals($this->activeMembership->id, $this->user->primaryMembership()->id);
    }

    /** @test */
    public function itCanHaveASecondaryMemberships()
    {
        $this->assertEquals(0, $this->user->secondaryMemberships->count());

        $this->user->memberships()->attach(factory(Membership::class)->states('secondary')->create()->id);

        $this->assertEquals(1, $this->user->fresh()->secondaryMemberships->count());
    }

    /** @test */
    public function itCanUpdatePrimaryMembership()
    {
        Carbon::setTestNow(Carbon::now()->micro(0));
        $this->assertNotEquals('Division', $this->user->primaryMembership()->name);
        $this->assertNull(Membership\MembershipPivot::where(['user_id' => $this->user->id, 'membership_id' => $this->activeMembership->id])->first()->ended_at);

        $this->assertTrue($this->user->updatePrimaryMembership('GBR', 'EUR'));
        // Test adding duplicate
        $this->assertFalse($this->user->updatePrimaryMembership('GBR', 'EUR'));

        $this->assertEquals('Division', $this->user->primaryMembership()->name);
        $this->assertEquals('GBR', $this->user->primaryMembership()->pivot->division);
        $this->assertEquals('EUR', $this->user->primaryMembership()->pivot->region);
        $this->assertEquals(Carbon::now(), $this->user->primaryMembership()->pivot->started_at);
        $this->assertNull($this->user->primaryMembership()->pivot->ended_at);

        $this->assertNotNull(Membership\MembershipPivot::where(['user_id' => $this->user->id, 'membership_id' => $this->activeMembership->id])->first()->ended_at);
    }

    /** @test */
    public function itCanUpdatePrimaryMembershipEvenInPrimaryMembershipDoesntChange()
    {
        $user = factory(User::class)->create();
        $this->assertTrue($user->updatePrimaryMembership('USA', 'USA-N'));
        $this->assertTrue($user->fresh()->updatePrimaryMembership('CAN', 'USA-N'));
        $this->assertFalse($user->fresh()->updatePrimaryMembership('CAN', 'USA-N'));

        $this->assertEquals(2, $user->fresh()->membershipHistory()->count());
    }

    /** @test */
    public function itHandlesNotFindingPrimaryMembership()
    {
        $this->mock(Membership::class, function (MockInterface $mock) {
            $mock->shouldReceive('findPrimaryByVATSIMLocality')
                ->andReturn(null);
        });

        $this->assertFalse($this->user->updatePrimaryMembership('GBR', 'EUR'));
    }

    /** @test */
    public function itCanAddSecondaryMembershipIfAllowed()
    {
        $this->assertEquals(0, $this->user->secondaryMemberships->count());
        $this->user->addSecondaryMembership(Membership::findByIdent(Membership::IDENT_VISITING));
        $this->assertEquals(1, $this->user->fresh()->secondaryMemberships->count());

        // Test adding duplicate
        $this->user->addSecondaryMembership(Membership::findByIdent(Membership::IDENT_VISITING));
        $this->assertEquals(1, $this->user->fresh()->secondaryMemberships->count());

        // Set a primary membership that doesn't allow secondaries
        $this->user->fresh()->updatePrimaryMembership('GBR', 'EUR');

        // Assert secondaries removed
        $this->assertEquals(0, $this->user->fresh()->secondaryMemberships->count());

        $this->expectException(PrimaryMembershipDoesntAllowSecondaryException::class);
        $this->user->addSecondaryMembership(Membership::findByIdent(Membership::IDENT_VISITING));
    }

    /** @test */
    public function itThrowsAnExceptionIfAddingAPrimaryMembershipThroughSecondary()
    {
        $this->expectException(MembershipNotSecondaryException::class);
        $this->user->addSecondaryMembership(Membership::findByIdent(Membership::IDENT_INTERNATIONAL));
    }

    /** @test */
    public function itDeterminesIfItHasMembership()
    {
        $this->assertTrue($this->user->hasMembership($this->activeMembership));
        $this->assertTrue($this->user->hasMembership($this->activeMembership->id));
        $this->assertFalse($this->user->hasMembership($this->pastMembership));
    }

    /** @test */
    public function itCanRemoveAMembership()
    {
        $this->assertEquals(1, $this->user->removeMembership($this->activeMembership));
        $this->assertEquals(0, $this->user->fresh()->memberships()->count());
    }

    /** @test */
    public function itHasWorkingAttributes()
    {
        $this->assertFalse($this->user->is_home_member);
        $this->user->updatePrimaryMembership('GBR', 'EUR');
        $this->assertTrue($this->user->is_home_member);

        $this->user->updatePrimaryMembership('EUD', 'EUR');
        $this->assertFalse($this->user->is_transferring);
        $this->user->addSecondaryMembership(Membership::findByIdent(Membership::IDENT_TRANSFERING));
        $this->assertTrue($this->user->is_transferring);

        $this->assertFalse($this->user->is_visiting);
        $this->user->removeSecondaryMemberships();
        $this->user->addSecondaryMembership(Membership::findByIdent(Membership::IDENT_VISITING));
        $this->assertTrue($this->user->is_visiting);
    }
}
