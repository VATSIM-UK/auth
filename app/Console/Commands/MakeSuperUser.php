<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class MakeSuperUser extends Command
{
    protected $signature = 'user:super {user}';

    /* @var User */
    protected $user;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function handle()
    {
        if (app()->environment() == "production") {
            $this->error("The app is in production! This command is disabled.");
            return;
        }

        if (! $this->user = $this->user::find($this->argument('user'))) {
            $this->info("A user was not found with the ID {$this->argument('user')}");
            return;
        }

        $this->user->givePermissionTo('*');

        $this->info("User with ID " . $this->user->id . " was made a super user!");
    }
}
