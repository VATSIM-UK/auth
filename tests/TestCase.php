<?php

namespace Tests;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Gate;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions;

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

    public function assertCollectionSubset($subset, $collection)
    {
        $this->assertEquals($subset, $subset->intersect($collection));
    }
}
