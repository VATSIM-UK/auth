<?php

namespace App\Events\User;

use App\Models\Ban;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BanRepealed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var User
     */
    private $user;
    /**
     * @var Ban
     */
    private $ban;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Ban $ban
     */
    public function __construct(Ban $ban)
    {
        $this->user = $ban->user;
        $this->ban = $ban;
    }
}
