<?php

namespace App\Events\User;

use App\Models\Ban;

class BanRepealed extends BaseUserEvent
{
    /**
     * @var Ban
     */
    private $ban;

    /**
     * Create a new event instance.
     *
     * @param  Ban  $ban
     */
    public function __construct(Ban $ban)
    {
        parent::__construct($ban->user);
        $this->ban = $ban;
    }
}
