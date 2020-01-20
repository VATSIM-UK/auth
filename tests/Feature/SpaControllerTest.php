<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaControllerTest extends TestCase
{
    const fakePath = '/some/path';

    public function testUnauthenticatedUserRedirected()
    {
        $this->get(self::fakePath)
            ->assertRedirect('/login')
            ->assertSessionHas('url.intended', url(self::fakePath));
    }

    public function testSemiAuthenticatedUserRedirected()
    {
        $this->actingAs($this->user, 'partial_web')
            ->get(self::fakePath)
            ->assertRedirect('/login')
            ->assertSessionHas('url.intended', url(self::fakePath));
    }

    public function testAuthenticatedUserNotRedirected()
    {
        $this->actingAs($this->user, 'web')
            ->get(self::fakePath)
            ->assertOk();
    }
}
