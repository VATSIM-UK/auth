<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Database\MockCoreDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->seedTables();

    }

    protected function seedTables()
    {
        if (!method_exists($this, 'beginDatabaseTransaction')) {
            return;
        }

        // Setup account table
        MockCoreDatabase::create();
    }

    protected function dropTabkes()
    {
        if (!method_exists($this, 'beginDatabaseTransaction')) {
            return;
        }

        // Setup account table
        MockCoreDatabase::destroy();
    }
}
