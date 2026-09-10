<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function user(Community $community = Community::FAD, string $role = 'anggota'): User
    {
        return User::factory()->create(['community' => $community, 'role' => $role]);
    }

    private function payload(User $u): array
    {
        return [
            'title' => 'Kegiatan Literasi',
            'category_id' => Category::for($u->community)->value('id'),
            'start_date' => '2026-05-01',
            'end_date' => null,
            'location' => 'Pelaihari',
            'description' => 'Deskripsi kegiatan literasi anak.',
        ];
    }

    public function test_guest_redirected(): void
    {
        $this->get('/fad/reports')->assertRedirect('/fad/login');
    }

    public function test_anggota_can_create_with_photos(): void
    {
        Storage::fake('public');
        $u = $this->user();
        Category::factory()->create(['community' => Community::FAD]);

        $res = $this->actingAs($u)->post('/fad/reports', $this->payload($u) + [
            'photos' => [UploadedFile::fake()->image('a.jpg', 800, 600)],
        ]);

        $report = Report::sole();
        $this->assertSame(Community::FAD, $report->community);
        $this->assertSame($u->id, $report->user_id);
        $res->assertRedirect(route('community.reports.show', ['community' => 'fad', 'report' => $report]));
        Storage::disk('public')->assertExists($report->photos()->value('path'));
    }

    public function test_anggota_cannot_edit_others_report(): void
    {
        $owner = $this->user();
        $other = $this->user();
        Category::factory()->create(['community' => Community::FAD]);
        $r = Report::factory()->for($owner)->create([
            'community' => Community::FAD,
            'category_id' => Category::for(Community::FAD)->value('id'),
        ]);

        $this->actingAs($other)->get("/fad/reports/{$r->id}/edit")->assertForbidden();
        $this->actingAs($other)->put("/fad/reports/{$r->id}", ['title' => 'x', 'category_id' => $r->category_id, 'start_date' => $r->start_date->format('Y-m-d'), 'description' => $r->description])->assertForbidden();
        $this->actingAs($other)->delete("/fad/reports/{$r->id}")->assertForbidden();
        $this->actingAs($owner)->get("/fad/reports/{$r->id}/edit")->assertOk();
    }

    public function test_admin_can_edit_others_report(): void
    {
        $admin = $this->user(role: 'admin');
        $r = Report::factory()->for($this->user())->create([
            'community' => Community::FAD,
            'category_id' => Category::factory()->create(['community' => Community::FAD])->id,
        ]);
        $this->actingAs($admin)->put("/fad/reports/{$r->id}", [
            'title' => 'Diedit admin',
            'category_id' => $r->category_id,
            'start_date' => $r->start_date->format('Y-m-d'),
            'description' => $r->description,
        ])->assertRedirect();
        $this->assertSame('Diedit admin', $r->fresh()->title);
    }

    public function test_genre_user_cannot_touch_fad_report(): void
    {
        $r = Report::factory()->create([
            'community' => Community::FAD,
            'category_id' => Category::factory()->create(['community' => Community::FAD])->id,
        ]);
        $g = $this->user(Community::GENRE);
        $this->actingAs($g)->get("/genre/reports/{$r->id}")->assertNotFound();
        $this->actingAs($g)->delete("/genre/reports/{$r->id}")->assertNotFound();
    }

    public function test_category_must_belong_to_same_community(): void
    {
        $u = $this->user();
        $genreCat = Category::factory()->create(['community' => Community::GENRE]);

        $this->actingAs($u)->post('/fad/reports', [
            'title' => 'X', 'category_id' => $genreCat->id,
            'start_date' => '2026-05-01', 'description' => 'Y',
        ])->assertSessionHasErrors('category_id');
        $this->assertSame(0, Report::count());
    }

    public function test_validation_rules(): void
    {
        $u = $this->user();
        Category::factory()->create(['community' => Community::FAD]);
        $catId = Category::for(Community::FAD)->value('id');

        $this->actingAs($u)->post('/fad/reports', [
            'title' => '', 'category_id' => $catId, 'start_date' => '2026-05-10',
            'end_date' => '2026-05-05', 'description' => '',
        ])->assertSessionHasErrors(['title', 'description', 'end_date']);
    }

    public function test_index_filters_paginates(): void
    {
        $u = $this->user();
        $cat = Category::factory()->create(['community' => Community::FAD]);
        Report::factory()->count(20)->for($u)->create([
            'community' => Community::FAD, 'category_id' => $cat->id, 'start_date' => '2026-02-01',
        ]);

        $this->actingAs($u)->get('/fad/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->component('Reports/Index')
                ->where('reports.total', 20)
                ->has('reports.data', Report::PER_PAGE));
        $this->actingAs($u)->get('/fad/reports?month=3&year=2026')
            ->assertInertia(fn ($p) => $p->where('reports.total', 0));
    }

    public function test_destroy_removes_files(): void
    {
        Storage::fake('public');
        $u = $this->user();
        $cat = Category::factory()->create(['community' => Community::FAD]);
        $r = Report::factory()->for($u)->create(['community' => Community::FAD, 'category_id' => $cat->id]);
        Storage::disk('public')->put('reports/fad/'.$r->id.'/x.jpg', 'dummy');
        $r->photos()->create(['path' => 'reports/fad/'.$r->id.'/x.jpg', 'original_name' => 'x.jpg']);

        $this->actingAs($u)->delete("/fad/reports/{$r->id}")->assertRedirect('/fad/reports');
        $this->assertSame(0, Report::count());
        Storage::disk('public')->assertMissing('reports/fad/'.$r->id.'/x.jpg');
    }
}
