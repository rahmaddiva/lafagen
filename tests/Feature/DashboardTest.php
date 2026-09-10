<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_props(): void
    {
        $u = User::factory()->create(['community' => Community::FAD]);
        $cat = Category::factory()->create(['community' => Community::FAD]);
        $year = now()->year;
        Report::factory()->count(3)->for($u)->create([
            'community' => Community::FAD,
            'category_id' => $cat->id,
            'start_date' => sprintf('%d-06-15', $year),
        ]);

        $monthIndex = 5; // Juni

        $this->actingAs($u)->get('/fad/dashboard')->assertOk()->assertInertia(
            fn ($p) => $p->component('Dashboard')
                ->where('stats.total_reports', 3)
                ->where('stats.total_members', 1)
                ->where('stats.this_year', 3)
                ->has('monthly', 12)
                ->where("monthly.{$monthIndex}.total", 3)
                ->has('recent', 3)
        );
    }

    public function test_dashboard_scoped_to_community(): void
    {
        $g = User::factory()->create(['community' => Community::GENRE]);
        Report::factory()->create([
            'community' => Community::FAD,
            'user_id' => User::factory()->create(['community' => Community::FAD])->id,
            'category_id' => Category::factory()->create(['community' => Community::FAD])->id,
        ]);
        $this->actingAs($g)->get('/genre/dashboard')
            ->assertInertia(fn ($p) => $p->where('stats.total_reports', 0));
    }

    public function test_fad_user_cannot_open_genre_dashboard(): void
    {
        $f = User::factory()->create(['community' => Community::FAD]);
        $this->actingAs($f)->get('/genre/dashboard')->assertRedirect('/fad/dashboard');
    }
}
