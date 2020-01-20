<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaControllerTest extends TestCase
{
    public function testUnauthenticatedUserRedirected()
    {
        $this->get('/some/path')
            ->assertRedirect('/login')
            ->assertSessionHas('url.intended', url('/some/path'));
    }

    public function testSemiAuthenticatedUserRedirected()
    {
        $this->actingAs($this->user, 'partial_web')
            ->get('/some/path')
            ->assertRedirect('/login')
            ->assertSessionHas('url.intended', url('/some/path'));
    }

    public function testAuthenticatedUserNotRedirected()
    {
        $this->actingAs($this->user, 'web')
            ->get('/some/path')
            ->assertOk();
    }
}
