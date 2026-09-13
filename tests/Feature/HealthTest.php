<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_serves_landing(): void
    {
        // Landing mengirim daftar komunitas (nama, logo, agregat anggota &
        // laporan) — butuh database untuk angkanya.
        $this->get('/')
            ->assertOk()
            ->assertInertia(
                fn ($page) => $page->component('Landing')->has('communities', 2)
            );
    }

    public function test_health_endpoint(): void
    {
        $this->get('/up')->assertOk();
    }
}
