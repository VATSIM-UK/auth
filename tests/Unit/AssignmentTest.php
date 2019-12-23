<?php

namespace Tests\Unit;

use App\Models\Permissions\Assignment;
use App\Models\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanGetTheRelatedModel()
    {
        $assignmentRole = factory(Assignment::class)->create();
        $assignmentUser = factory(Assignment::class, 'user')->create();

        $this->assertEquals(Role::class, get_class($assignmentRole->related));
        $this->assertEquals(User::class, get_class($assignmentUser->related));
    }
}
