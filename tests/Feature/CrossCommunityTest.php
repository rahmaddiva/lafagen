<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossCommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_fad_user_redirected_from_all_genre_pages(): void
    {
        $f = User::factory()->create(['community' => Community::FAD]);

        foreach (['dashboard', 'reports', 'categories', 'users'] as $path) {
            $this->actingAs($f)->get("/genre/{$path}")->assertRedirect('/fad/dashboard');
        }
        $this->actingAs($f)->post('/genre/logout')->assertRedirect('/fad/dashboard');
    }

    public function test_genre_user_redirected_from_fad_pages(): void
    {
        $g = User::factory()->create(['community' => Community::GENRE]);
        $this->actingAs($g)->get('/fad/dashboard')->assertRedirect('/genre/dashboard');
        $this->actingAs($g)->get('/fad/reports')->assertRedirect('/genre/dashboard');
    }

    public function test_logged_in_user_cannot_see_login_page(): void
    {
        $f = User::factory()->create(['community' => Community::FAD]);
        $this->actingAs($f)->get('/fad/login')->assertRedirect('/fad/dashboard');
    }

    public function test_login_regenerates_session(): void
    {
        $u = User::factory()->create(['community' => Community::FAD, 'password' => 'secret123']);

        $first = $this->post('/fad/login', ['email' => $u->email, 'password' => 'secret123']);
        $sid = $first->getSession()->getId();
        $first->assertRedirect('/fad/dashboard');

        $this->get('/fad/dashboard');
        $this->assertAuthenticatedAs($u);
        $this->assertNotSame($sid, $this->app['session.store']->getId() === null ? $sid : $this->app['session.store']->getId());
    }
}