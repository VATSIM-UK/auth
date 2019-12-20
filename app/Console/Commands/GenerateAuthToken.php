<?php


namespace App\Console\Commands;


use App\User;
use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;

class GenerateAuthToken extends Command
{
    protected $signature = "token:generate {user}";
    protected $user;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function handle()
    {
        try {
            app()->make(ClientRepository::class)->personalAccessClient();
        } catch (\RuntimeException $e) {
            if ($e->getMessage() == "Personal access client not found. Please create one.") {
                $this->info('No personal access client has been setup. Create one with "php artisan passport:client --personal"');
                return;
            }
            throw $e;
        }

        if (!$user = $this->user::find($this->argument('user'))) {
            $this->info("A user was not found with the ID {$this->argument('user')}");
            return;
        }

        $token = $user->createToken('ConsoleToken')->accessToken;

        $this->info("Success! Token: {$token}");
    }
}
