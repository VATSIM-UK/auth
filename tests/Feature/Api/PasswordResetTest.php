<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions, MakesGraphQLRequests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user->setPassword('12345678');
    }

    public function testUnauthenticatedCantAccessMethods()
    {
        $this->graphQL('
        mutation{
            updatePassword(
                old_password: "12345678",
                new_password: "Testing12345"
            ),
            removePassword (
                current_password: "12345678"
            )
        }
        ')->assertJsonPath('errors.0.debugMessage', 'Unauthenticated.')
            ->assertJsonPath('errors.1.debugMessage', 'Unauthenticated.');
    }

    public function testUpdatingPasswordRequiresCorrectOldPassword()
    {
        $this->actingAs($this->user, 'api');
        $this->assertFalse($this->user->verifyPassword('Testing12345'));
        $this->graphQL('
        mutation{
            updatePassword(
                old_password: "12345WrongPassword",
                new_password: "Testing12345"
            )
        }
        ')->assertJsonPath('errors.0.extensions.validation.old_password.0', 'Incorrect previous password given');

        $this->graphQL('
        mutation{
            updatePassword(
                old_password: "12345678",
                new_password: "Testing12345"
            )
        }
        ')->assertJsonPath('data.updatePassword', true);
        $this->assertTrue($this->user->fresh()->verifyPassword('Testing12345'));
    }

    public function testItRequiresCorrectLevelOfPasswordSecurity()
    {
        $this->actingAs($this->user, 'api');
        $this->assertFalse($this->user->fresh()->verifyPassword('Testing1'));
        $this->graphQL('
        mutation{
            updatePassword(
                old_password: "12345678",
                new_password: "Testin"
            )
        }
        ')->assertJsonPath('errors.0.extensions.validation.new_password.0', trans('validation.min.string', ['attribute' => 'new password', 'min' => 8]));

        $this->graphQL('
        mutation{
            updatePassword(
                old_password: "12345678",
                new_password: "testing1"
            )
        }
        ')->assertJsonPath('errors.0.extensions.validation.new_password.0', trans('validation.uppercase', ['attribute' => 'new password']));

        $this->graphQL('
        mutation{
            updatePassword(
                old_password: "12345678",
                new_password: "TESTING1"
            )
        }
        ')->assertJsonPath('errors.0.extensions.validation.new_password.0', trans('validation.lowercase', ['attribute' => 'new password']));

        $this->graphQL('
        mutation{
            updatePassword(
                old_password: "12345678",
                new_password: "Testing1"
            )
        }
        ')->assertJsonPath('data.updatePassword', true);
        $this->assertTrue($this->user->fresh()->verifyPassword('Testing1'));
    }

    public function testUserWithNoPasswordCanAddPassword()
    {
        $this->actingAs($this->user, 'api');
        $this->user->removePassword();
        $this->assertFalse($this->user->fresh()->has_password);

        $this->graphQL('
        mutation{
            updatePassword(
                new_password: "Testin"
            )
        }
        ')->assertJsonPath('errors.0.extensions.validation.new_password.0', 'The new password must be at least 8 characters.');

        $this->graphQL('
        mutation{
            updatePassword(
                new_password: "Testing1"
            )
        }
        ')->assertJsonPath('data.updatePassword', true);
        $this->assertTrue($this->user->fresh()->verifyPassword('Testing1'));
    }

    public function testUserCantUpdateWithSamePassword()
    {
        $this->actingAs($this->user, 'api');

        $this->graphQL('
        mutation{
            updatePassword(
                old_password: "12345678"
                new_password: "12345678"
            )
        }
        ')->assertJsonPath('errors.0.extensions.validation.old_password.0', 'The old password and new password must be different.');
    }

    public function testUserWithNoPasswordCantRemovePassword()
    {
        $this->actingAs($this->user, 'api');
        $this->user->removePassword();
        $this->assertFalse($this->user->fresh()->has_password);

        $this->graphQL('
        mutation{
            removePassword(
                current_password: "NoPasswordH3r3"
            )
        }
        ')->assertJsonPath('data.removePassword', false);
    }

    public function testUserWithPasswordCanRemovePassword()
    {
        $this->actingAs($this->user, 'api');

        $this->graphQL('
        mutation{
            removePassword(
                current_password: "Testin"
            )
        }
        ')->assertJsonPath('errors.0.extensions.validation.current_password.0', 'Incorrect current password given');

        $this->graphQL('
        mutation{
            removePassword(
                current_password: "12345678"
            )
        }
        ')->assertJsonPath('data.removePassword', true);

        $this->assertFalse($this->user->fresh()->has_password);
    }
}
