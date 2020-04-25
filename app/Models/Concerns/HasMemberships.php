<?php


namespace App\Models\Concerns;


use App\Exceptions\Memberships\MembershipNotSecondaryException;
use App\Exceptions\Memberships\PrimaryMembershipDoesntAllowSecondaryException;
use App\Models\Membership;
use App\Models\Membership\MembershipPivot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasMemberships
{
    public function memberships(): BelongsToMany
    {
        return $this->membershipsRelationship()
            ->wherePivot('ended_at', null)
            ->orderByPriority();
    }

    public function membershipHistory(): BelongsToMany
    {
        return $this->membershipsRelationship()
            ->orderBy((new MembershipPivot())->getTable().'.started_at', 'desc');
    }

    public function primaryMembership(): ?Membership
    {
        return $this->memberships()
            ->primary()
            ->first();
    }

    public function secondaryMemberships(): BelongsToMany
    {
        return $this->memberships()
            ->secondary()
            ->orderByPriority();
    }

    public function updatePrimaryMembership(string $division, string $region): bool
    {
        $matchingMembership = Membership::findPrimaryByVATSIMLocality($division, $region);

        if(!$matchingMembership){
            return false;
        }

        // Check if the user already has this membership
        if($this->hasMembership($matchingMembership)){
            return false;
        }

        // Check we can have secondary memberships
        if(!$matchingMembership->can_have_secondaries){
            $this->removeSecondaryMemberships();
        }

        // End Previous Primary Membership
        if($currentMembership = $this->primaryMembership()){
            $this->removeMembership($currentMembership);
        }

        $this->memberships()->attach($matchingMembership, [
            'division' => $division,
            'region' => $region
        ]);

        return true;
    }

    public function addSecondaryMembership(Membership $membership): bool
    {
        throw_if($membership->primary, new MembershipNotSecondaryException());

        // Check we can have secondary memberships
        if($this->primaryMembership() && !$this->primaryMembership()->can_have_secondaries){
            throw new PrimaryMembershipDoesntAllowSecondaryException();
        }

        // Check if the user already has this membership
        if($this->hasMembership($membership)){
            return false;
        }

        $this->memberships()->attach($membership);

        return true;
    }

    public function removeSecondaryMemberships(): void
    {
        $this->secondaryMemberships->each(function ($membership) {
           $this->removeMembership($membership);
        });
    }

    public function hasMembership(Membership $membership): bool
    {
        return $this->memberships()->where('memberships.id', $membership->id)->exists();
    }

    public function removeMembership(Membership $state): int
    {
        return $this->memberships()->updateExistingPivot($state, [
            'ended_at' => Carbon::now(),
        ]);
    }

    public function getPrimaryMembershipAttribute()
    {
        return $this->primaryMembership();
    }

    private function membershipsRelationship(): BelongsToMany
    {
        return $this->belongsToMany(Membership::class, 'user_memberships')
            ->using(MembershipPivot::class)
            ->withPivot('region', 'division','started_at', 'ended_at');
    }
}
