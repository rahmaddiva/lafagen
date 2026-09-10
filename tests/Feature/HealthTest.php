<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_serves_landing(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Welcome'));
    }

    public function test_health_endpoint(): void
    {
        $this->get('/up')->assertOk();
    }
}
