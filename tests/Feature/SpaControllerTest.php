<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaControllerTest extends TestCase
{
    const FAKE_PATH = '/some/path';

    public function testUnauthenticatedUserRedirected()
    {
        $this->get(self::FAKE_PATH)
            ->assertRedirect('/login')
            ->assertSessionHas('url.intended', url(self::FAKE_PATH));
    }

    public function testSemiAuthenticatedUserRedirected()
    {
        $this->actingAs($this->user, 'partial_web')
            ->get(self::FAKE_PATH)
            ->assertRedirect('/login')
            ->assertSessionHas('url.intended', url(self::FAKE_PATH));
    }

    public function testAuthenticatedUserNotRedirected()
    {
        $this->actingAs($this->user, 'web')
            ->get(self::FAKE_PATH)
            ->assertOk();
    }
}
