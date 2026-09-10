<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtered_never_leaks_across_communities(): void
    {
        Report::factory()->create(['community' => Community::FAD]);
        $genre = Report::factory()->create(['community' => Community::GENRE]);

        $fadIds = Report::filtered(Community::FAD, [])->pluck('id')->all();
        $this->assertNotContains($genre->id, $fadIds);
    }
}
