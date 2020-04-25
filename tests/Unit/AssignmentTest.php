<?php

namespace Tests\Unit;

use App\Models\Permissions\Assignment;
use App\Models\Role;
use App\User;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    /** @test */
    public function itCanGetTheRelatedModel()
    {
        $assignmentRole = factory(Assignment::class)->create();
        $assignmentUser = factory(Assignment::class)->state('user')->create();

        $this->assertEquals(Role::class, get_class($assignmentRole->related));
        $this->assertEquals(User::class, get_class($assignmentUser->related));
    }
}
