<?php

namespace Tests;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Database\MockCoreDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow();

        // Setup account table
        MockCoreDatabase::create();

        $this->user = factory(User::class)->create();
    }
}
