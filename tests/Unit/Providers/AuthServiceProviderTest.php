<?php

namespace Tests\Unit\Providers;

use App\Models\Permissions\Assignment;
use Tests\TestCase;

class AuthServiceProviderTest extends TestCase
{
    /** @test */
    public function itRegistersCustomPermissionsOnGate()
    {
        factory(Assignment::class)->state('user')->create(
            [
                'related_id' => $this->user->id,
                'permission' => 'test.permission',
            ]
        );

        $this->assertTrue($this->user->can('test.permission'));
        $this->assertFalse($this->user->can('test.core'));
    }
}
