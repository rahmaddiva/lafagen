<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardQueryCountTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<string> */
    private function dashboardQueries(): array
    {
        $user = User::factory()->create(['community' => Community::FAD]);
        $queries = [];
        DB::listen(function ($q) use (&$queries) {
            $queries[] = $q->sql;
        });

        $this->actingAs($user)->get('/fad/dashboard')->assertOk();

        return $queries;
    }

    public function test_monthly_block_is_one_aggregate_query(): void
    {
        $queries = $this->dashboardQueries();

        // Agregat bulanan dikenali dari alias "as m" + GROUP BY.
        $monthly = array_filter(
            $queries,
            fn ($sql) => str_contains($sql, 'group by') && str_contains($sql, ' as m'),
        );

        $this->assertCount(1, $monthly, 'Blok bulanan harus satu query agregat.');
    }

    public function test_dashboard_does_not_run_one_query_per_month(): void
    {
        $queries = $this->dashboardQueries();

        // Kode lama: 12 count() terpisah (satu per bulan) + query lain = ~22.
        // Sekarang harus jauh di bawah itu.
        $this->assertLessThanOrEqual(
            16,
            count($queries),
            'Dashboard menjalankan terlalu banyak query; loop 12 bulan mungkin kembali.',
        );
    }
}
