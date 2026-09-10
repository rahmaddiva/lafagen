<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_downloads_with_filters(): void
    {
        $u = User::factory()->create(['community' => Community::FAD, 'role' => 'admin']);
        $cat = Category::factory()->create(['community' => Community::FAD]);
        Report::factory()->count(2)->for($u)->create([
            'community' => Community::FAD, 'category_id' => $cat->id, 'start_date' => '2026-03-10',
        ]);
        Report::factory()->for($u)->create([
            'community' => Community::FAD, 'category_id' => $cat->id, 'start_date' => '2026-05-20',
        ]);

        $this->actingAs($u)->get('/fad/reports/export?month=3&year=2026')
            ->assertOk()
            ->assertDownload('laporan-fad-2026-03.xlsx');
    }

    public function test_export_session_guest_denied(): void
    {
        $this->get('/fad/reports/export')->assertRedirect('/fad/login');
    }
}