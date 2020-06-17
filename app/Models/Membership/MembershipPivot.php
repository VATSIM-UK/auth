<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MembershipPivot extends Pivot
{
    protected $table = 'user_memberships';
    protected $fillable = [
        'division',
        'region',
        'started_at',
        'ended_at',
    ];

    protected $dates = [
        'started_at',
        'ended_at',
    ];
}
