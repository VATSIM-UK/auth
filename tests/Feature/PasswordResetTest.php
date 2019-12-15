<?php


namespace Tests\Feature;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user->password = "1234Testing";
        $this->user->save();
    }

    public function testUserRedirectedIfDoestHavePassword()
    {
        $this->user->removePassword();
        $this->actingAs($this->user);

        $this->get(route('password.request'))->assertRedirect();
        $this->get(route('password.reset', '123'))->assertRedirect();
    }

    public function testUserRedirectedIfFullyAuthenticated()
    {
        $this->actingAs($this->user);

        $this->get(route('password.request'))->assertRedirect();
        $this->get(route('password.reset', '123'))->assertRedirect();
    }

    public function testUserCanSeeLinkToResetPassword()
    {
        $this->actingAs($this->user, 'partial_web');

        $this->get(route('login.secondary'))
            ->assertSeeText('Forgot your password?');
    }

    public function testUserCanVisitRequestLinkPage()
    {
        $this->actingAs($this->user, 'partial_web');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSeeText("Send Password Reset Link");
    }

    public function testUserCanRequestResetLink()
    {
        Notification::fake();

        $this->actingAs($this->user, 'partial_web');

        $this->followingRedirects()
            ->from(route('password.request'))->post(route('password.email'))
            ->assertSeeText("We have e-mailed your password reset link!");

        Notification::assertSentTo($this->user, ResetPassword::class);
    }

    public function testUserCanViewPasswordResetPage()
    {
        $token = Password::broker()->createToken($this->user);
        $this->actingAs($this->user, 'partial_web');

        $this->get(route('password.reset', $token))
            ->assertOk()
            ->assertSeeText("Password Reset")
            ->assertSeeText("Confirm Password")
            ->assertSeeText("Reset Password");
    }

    public function testUserCanResetPassword()
    {
        $token = Password::broker()->createToken($this->user);
        $this->actingAs($this->user, 'partial_web');

        $this->followingRedirects()
            ->from(route('password.reset', $token))
            ->post(route('password.update'), [
                'token' => $token,
                'password' => "SecretSt1ng",
                'password_confirmation' => "SecretSt1ng",
            ])->assertLocation('/');
        $this->assertAuthenticatedAs($this->user, 'web');
        $this->assertTrue($this->user->fresh()->verifyPassword('SecretSt1ng'));

    }
}
