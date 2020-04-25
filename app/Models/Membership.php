<?php

namespace App\Models;

use App\Models\Membership\MembershipPivot;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Membership extends Model
{
    const IDENT_DIVISION = 'DIV';
    const IDENT_REGION = 'REG';
    const IDENT_INTERNATIONAL = 'INT';
    const IDENT_VISITING = 'VIS';
    const IDENT_TRANSFERING = 'TFR';

    public $timestamps = false;

    protected $casts = [
        'primary' => 'bool',
        'can_have_secondaries' => 'bool',
    ];

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('primary', true);
    }

    public function scopeSecondary(Builder $query): Builder
    {
        return $query->where('primary', false);
    }

    public function scopeOrderByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority');
    }

    public static function findByIdent(string $ident): ?self
    {
        return self::where('identifier', $ident)->first();
    }

    public static function findPrimaryByVATSIMLocality(string $division, string $region): ?self
    {
        return self::primary()->orderByPriority()->get()->first(function ($membership) use ($division, $region) {
            $divisions = $membership->division_expression;
            $regions = $membership->region_expression;

            $regionCriteriaMatched = $regions ? ($regions->first() == '*' || $regions->contains($region)) : false;
            $divisionCriteriaMatched = $divisions ? ($divisions->first() == '*' || $divisions->contains($division)) : false;

            if ($regionCriteriaMatched && $divisionCriteriaMatched) {
                return true;
            }

            return false;
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_memberships')
            ->using(MembershipPivot::class);
    }

    public function getSecondaryAttribute(): bool
    {
        return ! $this->primary;
    }

    public function getDivisionExpressionAttribute(): ?Collection
    {
        return $this->attributes['division_expression'] ? collect(explode(',', $this->attributes['division_expression'])) : null;
    }

    public function getRegionExpressionAttribute(): ?Collection
    {
        return $this->attributes['region_expression'] ? collect(explode(',', $this->attributes['region_expression'])) : null;
    }
}
