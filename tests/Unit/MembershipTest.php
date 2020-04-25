<?php

namespace Tests\Unit;

use App\Models\Membership;
use App\User;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    /** @test */
    public function itHasWorkingScopes()
    {
        $this->assertEquals(3, Membership::primary()->count());
        $this->assertEquals(2, Membership::secondary()->count());

        $priorityOrderedMemberships = Membership::orderByPriority()->get();
        $this->assertEquals(Membership::IDENT_DIVISION, $priorityOrderedMemberships->first()->identifier);
        $this->assertEquals(Membership::IDENT_TRANSFERING, $priorityOrderedMemberships[1]->identifier);
    }

    /** @test */
    public function itCanFindByIdentity()
    {
        $this->assertEquals('International', Membership::findByIdent(Membership::IDENT_INTERNATIONAL)->name);
        $this->assertEquals('Visiting', Membership::findByIdent(Membership::IDENT_VISITING)->name);
        $this->assertEquals(null, Membership::findByIdent('UKN'));
    }

    /** @test */
    public function itCanFindByVATSIMLocality()
    {
        $this->assertEquals('Division', Membership::findPrimaryByVATSIMLocality('GBR', 'EUR')->name);
        $this->assertEquals('Region', Membership::findPrimaryByVATSIMLocality('EUD', 'EUR')->name);
        $this->assertEquals('International', Membership::findPrimaryByVATSIMLocality('USA-N', 'USA')->name);
    }

    /** @test */
    public function itCanHaveManyUsers()
    {
        $membership = Membership::findByIdent(Membership::IDENT_DIVISION);
        $users = factory(User::class, 3)->create()->each(function (User $user) use ($membership) {
            $membership->users()->attach($user->id);
        });

        $this->assertEquals(3, $membership->users()->count());
    }

    /** @test */
    public function itCanDetermineIfSecondary()
    {
        $this->assertFalse(Membership::findByIdent(Membership::IDENT_DIVISION)->secondary);
        $this->assertTrue(Membership::findByIdent(Membership::IDENT_VISITING)->secondary);
    }
}
