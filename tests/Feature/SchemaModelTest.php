<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_casts(): void
    {
        $user = User::factory()->create([
            'community' => Community::FAD,
            'role' => UserRole::ADMIN,
        ]);
        $fresh = $user->fresh();
        $this->assertSame(Community::FAD, $fresh->community);
        $this->assertSame(UserRole::ADMIN, $fresh->role);
        $this->assertTrue($fresh->isAdmin());
    }

    public function test_filtered_scopes_by_community_and_filters(): void
    {
        $fadCat = Category::factory()->create(['community' => Community::FAD]);
        Report::factory()->create([
            'community' => Community::FAD,
            'category_id' => $fadCat->id,
            'start_date' => '2026-03-10',
        ]);
        Report::factory()->create([
            'community' => Community::FAD,
            'category_id' => $fadCat->id,
            'start_date' => '2026-04-20',
        ]);
        Report::factory()->create([
            'community' => Community::GENRE,
            'category_id' => Category::factory()->create(['community' => Community::GENRE])->id,
            'start_date' => '2026-03-15',
        ]);

        $this->assertSame(2, Report::filtered(Community::FAD, [])->count());
        $this->assertSame(1, Report::filtered(Community::FAD, ['year' => 2026, 'month' => 3])->count());
        $this->assertSame(0, Report::filtered(Community::FAD, ['year' => 2026, 'month' => 5])->count());
    }

    public function test_category_unique_per_community(): void
    {
        Category::factory()->create(['community' => Community::FAD, 'name' => 'Pendidikan']);
        $this->expectException(QueryException::class);
        Category::factory()->create(['community' => Community::FAD, 'name' => 'Pendidikan']);
    }

    public function test_seeders_run(): void
    {
        $this->seed();
        $this->assertSame(2, User::where('role', UserRole::ADMIN->value)->count());
        $this->assertSame(12, Category::count());
        $this->assertSame(20, Report::count());
    }
}
