<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Buat laporan pada tanggal tertentu untuk komunitas tertentu.
     *
     * @param  string  $date  format Y-m-d
     */
    private function reportAt(Community $community, string $date): Report
    {
        $user = User::factory()->create(['community' => $community]);
        $category = Category::factory()->create(['community' => $community]);

        return Report::factory()->for($user)->create([
            'community' => $community,
            'category_id' => $category->id,
            'start_date' => $date,
        ]);
    }

    /** Baca nilai streak dari props halaman dashboard. */
    private function streak(Community $community): int
    {
        $user = User::factory()->create(['community' => $community]);
        $prefix = $community->value;

        $value = null;
        $this->actingAs($user)->get("/{$prefix}/dashboard")->assertInertia(
            function ($page) use (&$value) {
                $value = $page->toArray()['props']['activity']['streak'];

                return $page;
            }
        );

        return (int) $value;
    }

    public function test_streak_counts_consecutive_months_ending_now(): void
    {
        // Laporan bulan ini, bulan lalu, dua bulan lalu -> streak 3.
        $this->reportAt(Community::FAD, now()->format('Y-m-05'));
        $this->reportAt(Community::FAD, now()->subMonthNoOverflow()->format('Y-m-05'));
        $this->reportAt(Community::FAD, now()->subMonthsNoOverflow(2)->format('Y-m-05'));

        $this->assertSame(3, $this->streak(Community::FAD));
    }

    public function test_streak_is_zero_when_current_month_is_empty(): void
    {
        // Ada laporan bulan lalu, tapi bulan berjalan kosong -> rentetan putus.
        $this->reportAt(Community::FAD, now()->subMonthNoOverflow()->format('Y-m-05'));

        $this->assertSame(0, $this->streak(Community::FAD));
    }

    public function test_streak_stops_at_first_empty_month(): void
    {
        // Bulan ini ada, bulan -1 kosong, bulan -2 ada -> streak 1, bukan 2.
        $this->reportAt(Community::FAD, now()->format('Y-m-05'));
        $this->reportAt(Community::FAD, now()->subMonthsNoOverflow(2)->format('Y-m-05'));

        $this->assertSame(1, $this->streak(Community::FAD));
    }

    public function test_streak_is_zero_without_any_reports(): void
    {
        $this->assertSame(0, $this->streak(Community::FAD));
    }

    public function test_streak_counts_multiple_reports_in_one_month_once(): void
    {
        $this->reportAt(Community::FAD, now()->format('Y-m-05'));
        $this->reportAt(Community::FAD, now()->format('Y-m-20'));
        $this->reportAt(Community::FAD, now()->format('Y-m-28'));

        $this->assertSame(1, $this->streak(Community::FAD));
    }

    public function test_streak_is_scoped_to_community(): void
    {
        // Laporan GENRE tidak boleh menambah streak FAD.
        $this->reportAt(Community::GENRE, now()->format('Y-m-05'));
        $this->reportAt(Community::GENRE, now()->subMonthNoOverflow()->format('Y-m-05'));

        $this->assertSame(0, $this->streak(Community::FAD));
        $this->assertSame(2, $this->streak(Community::GENRE));
    }
}
