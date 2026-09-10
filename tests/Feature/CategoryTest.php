<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_anggota_forbidden(): void
    {
        $u = User::factory()->create(['community' => Community::FAD, 'role' => 'anggota']);
        $this->actingAs($u)->get('/fad/categories')->assertForbidden();
        $this->actingAs($u)->post('/fad/categories', ['name' => 'Baru'])->assertForbidden();
    }

    public function test_admin_creates_context_scoped(): void
    {
        $admin = User::factory()->create(['community' => Community::FAD, 'role' => 'admin']);
        $this->actingAs($admin)->post('/fad/categories', ['name' => 'Kewirausahaan'])
            ->assertRedirect('/fad/categories');
        $this->assertSame(1, Category::for(Community::FAD)->where('name', 'Kewirausahaan')->count());
        $this->assertSame(0, Category::for(Community::GENRE)->where('name', 'Kewirausahaan')->count());
    }

    public function test_index_scoped_to_admin_community(): void
    {
        $admin = User::factory()->create(['community' => Community::GENRE, 'role' => 'admin']);
        Category::factory()->create(['community' => Community::FAD, 'name' => 'Pendidikan']);
        Category::factory()->create(['community' => Community::GENRE, 'name' => 'Kesehatan']);

        $this->actingAs($admin)->get('/genre/categories')->assertOk()->assertInertia(
            fn ($p) => $p->component('Categories/Index')
                ->has('categories', 1)
                ->where('categories.0.name', 'Kesehatan')
        );
    }

    public function test_admin_cannot_edit_fad_category_via_genre(): void
    {
        $admin = User::factory()->create(['community' => Community::GENRE, 'role' => 'admin']);
        $fad = Category::factory()->create(['community' => Community::FAD, 'name' => 'Pendidikan']);

        $this->actingAs($admin)->put("/genre/categories/{$fad->id}", ['name' => 'Hax'])
            ->assertNotFound();
        $this->assertSame('Pendidikan', $fad->fresh()->name);
    }

    public function test_delete_blocked_when_in_use(): void
    {
        $admin = User::factory()->create(['community' => Community::FAD, 'role' => 'admin']);
        $cat = Category::factory()->create(['community' => Community::FAD, 'name' => 'Dipakai']);
        Report::factory()->create([
            'community' => Community::FAD,
            'user_id' => User::factory()->create(['community' => Community::FAD])->id,
            'category_id' => $cat->id,
        ]);

        $this->actingAs($admin)->delete("/fad/categories/{$cat->id}")
            ->assertRedirect('/fad/categories');
        $this->assertNotNull($cat->fresh());
        $this->assertDatabaseHas('categories', ['id' => $cat->id]);
    }

    public function test_delete_unused_succeeds(): void
    {
        $admin = User::factory()->create(['community' => Community::FAD, 'role' => 'admin']);
        $cat = Category::factory()->create(['community' => Community::FAD, 'name' => 'Kosong']);
        $this->actingAs($admin)->delete("/fad/categories/{$cat->id}")->assertRedirect('/fad/categories');
        $this->assertNull(Category::find($cat->id));
    }

    public function test_duplicate_name_same_community_rejected(): void
    {
        $admin = User::factory()->create(['community' => Community::FAD, 'role' => 'admin']);
        Category::factory()->create(['community' => Community::FAD, 'name' => 'Pendidikan']);
        $this->actingAs($admin)->post('/fad/categories', ['name' => 'Pendidikan'])
            ->assertSessionHasErrors('name');
    }
}