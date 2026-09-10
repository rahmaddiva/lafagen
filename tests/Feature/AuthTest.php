<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_renders(): void
    {
        $this->get('/')->assertOk()
            ->assertInertia(fn ($p) => $p->component('Landing')->where('community', null));
    }

    public function test_login_page_per_community(): void
    {
        $this->get('/fad/login')->assertOk()->assertInertia(
            fn ($p) => $p->component('Auth/Login')
                ->where('community.key', 'fad')
                ->where('community.title', 'FAD Tanah Laut')
        );
        $this->get('/genre/login')->assertOk()
            ->assertInertia(fn ($p) => $p->where('community.key', 'genre'));
    }

    public function test_unknown_community_404(): void
    {
        $this->get('/xxx/login')->assertNotFound();
    }

    public function test_guest_redirected_to_matching_login(): void
    {
        $this->get('/genre/dashboard')->assertRedirect('/genre/login');
    }
}
