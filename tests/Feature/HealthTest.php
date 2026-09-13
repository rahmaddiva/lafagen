<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_serves_landing(): void
    {
        // Landing kini mengirim `counts` per komunitas (2 query agregat), jadi
        // halaman ini memang butuh database.
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Landing'));
    }

    public function test_health_endpoint(): void
    {
        $this->get('/up')->assertOk();
    }
}
