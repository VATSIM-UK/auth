<?php

namespace Tests;

use App\Passport\Client;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /* @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow();

        $this->user = factory(User::class)->create();
    }

    public function withoutPermissions()
    {
        Gate::before(function () {
            return true;
        });
    }

    public function asMachineMachine()
    {
        Passport::actingAsClient(new Client(), ['machine-only']);
    }

    public function asUserOnAPI()
    {
        Passport::actingAs($this->user);
    }

    public function assertCollectionSubset($subset, $collection)
    {
        $this->assertEquals($subset, $subset->intersect($collection));
    }
}
