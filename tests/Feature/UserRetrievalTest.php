<?php


namespace Tests\Feature;


use Laravel\Passport\Passport;
use Tests\TestCase;

class UserRetrievalTest extends TestCase
{
    /** @test */
    public function testUnauthenticatedUserCannotAccessUserInformation()
    {
        $this->json('GET', route('api.user'))
            ->assertUnauthorized();
    }

    /** @test */
    public function testAuthenticatedUserCanAccessTheirInformation()
    {
        Passport::actingAs($this->user);

        $this->json('GET', route('api.user'))
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $this->user->id,
                'email' => $this->user->email,
            ]);
    }
}
