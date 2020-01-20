<?php

namespace App\Events\User;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseUserEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
