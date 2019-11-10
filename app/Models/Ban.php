<?php

namespace App\Models;

use App\Constants\BanConstants;
use App\Events\User\BanRepealed;
use App\Models\Ban\Reason;
use App\User;
use BenSampo\Enum\Traits\CastsEnums;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Ban extends Model
{
    use CastsEnums;

    protected $table = "bans";
    protected $enumCasts = [
        'type' => BanConstants::class,
    ];
    public $timestamps = [
        'starts_at',
        'ends_at',
        'repealed_at'
    ];

    public function scopeLocal(Builder $query)
    {
        return $query->where('type', BanConstants::LOCAL);
    }

    public function scopeNetwork(Builder $query)
    {
        return $query->where('type', BanConstants::NETWORK);
    }

    public function scopeNotRepealed(Builder $query)
    {
        return $query->whereNull('repealed_at');
    }

    public function scopeRepealed(Builder $query)
    {
        return $query->whereNotNull('repealed_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function banner()
    {
        return $this->belongsTo(User::class, 'banner_id');
    }

    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

    public function end()
    {
        if (!$this->ends_at){
            $this->ends_at = Carbon::now();
            $this->save();
        }
    }

    public function repeal()
    {
        $this->repealed_at = Carbon::now();
        $this->save();
        event(new BanRepealed($this));
    }

    public function getIsLocalAttribute()
    {
        return $this->type->is(BanConstants::LOCAL);
    }

    public function getIsNetworkAttribute()
    {
        return $this->type->is(BanConstants::NETWORK);
    }
}
