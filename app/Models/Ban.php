<?php

namespace App\Models;

use App\Constants\BanTypeConstants;
use App\Events\User\BanRepealed;
use App\Models\Ban\Reason;
use App\User;
use BenSampo\Enum\Traits\CastsEnums;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ban extends Model
{
    use CastsEnums;

    protected $table = 'bans';
    protected $enumCasts = [
        'type' => BanTypeConstants::class,
    ];
    public $dates = [
        'starts_at',
        'ends_at',
        'repealed_at',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('ends_at', '>', Carbon::now())
            ->orWhereNull('ends_at');
    }

    public function scopeLocal(Builder $query): Builder
    {
        return $query->where('type', BanTypeConstants::LOCAL);
    }

    public function scopeNetwork(Builder $query): Builder
    {
        return $query->where('type', BanTypeConstants::NETWORK);
    }

    public function scopeNotRepealed(Builder $query): Builder
    {
        return $query->whereNull('repealed_at');
    }

    public function scopeRepealed(Builder $query): Builder
    {
        return $query->whereNotNull('repealed_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banner_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(Reason::class);
    }

    public function end(): bool
    {
        if (! $this->ends_at) {
            $this->ends_at = Carbon::now();
            $this->save();

            return true;
        }

        return false;
    }

    public function repeal(): bool
    {
        $this->repealed_at = Carbon::now();
        $result = $this->save();
        event(new BanRepealed($this));

        return $result;
    }

    public function getIsActiveAttribute(): bool
    {
        return !$this->ends_at || $this->ends_at->isFuture();
    }

    public function getIsLocalAttribute(): bool
    {
        return $this->type->is(BanTypeConstants::LOCAL);
    }

    public function getIsNetworkAttribute(): bool
    {
        return $this->type->is(BanTypeConstants::NETWORK);
    }
}
