<?php

namespace Tests;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Gate;
use Tests\Database\MockCoreDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /* @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow();

        // Setup account table
        MockCoreDatabase::create();

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
