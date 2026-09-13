<?php

namespace App\Http\Controllers;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $community = $request->attributes->get('community');
        $year = (int) $request->query('year', now()->year);
        $year = max(2020, min(now()->year + 1, $year));

        $totalReports = Report::filtered($community)->count();
        $totalMembers = User::query()->where('community', $community->value)->count();
        $thisMonth = Report::filtered($community, ['year' => now()->year, 'month' => now()->month])->count();
        $thisYear = Report::filtered($community, ['year' => $year])->count();

        $prev = now()->startOfMonth()->subMonthNoOverflow();
        $prevMonth = Report::filtered($community, [
            'year' => $prev->year,
            'month' => $prev->month,
        ])->count();

        $monthly = $this->monthlyTotals($community, $year);

        $byCategory = Category::query()
            ->where('categories.community', $community->value)
            ->leftJoin('reports', function ($join) use ($year) {
                $join->on('reports.category_id', '=', 'categories.id')
                    ->whereYear('reports.start_date', $year);
            })
            ->select('categories.name', DB::raw('count(reports.id) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'total' => (int) $row->total])
            ->all();

        $recent = Report::filtered($community, ['year' => $year])
            ->with(['category:id,name', 'user:id,name'])
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'category_name' => $r->category->name,
                'start_date' => $r->start_date->format('Y-m-d'),
                'user_name' => $r->user->name,
                'created_ago' => $r->created_at->diffForHumans(),
            ])->all();

        return Inertia::render('Dashboard', [
            'title' => 'Dashboard',
            'community' => $community->config(),
            'stats' => [
                'total_reports' => $totalReports,
                'total_members' => $totalMembers,
                'this_month' => $thisMonth,
                'this_year' => $thisYear,
                'prev_month' => $prevMonth,
            ],
            'activity' => $this->activity($community),
            'monthly' => $monthly,
            'byCategory' => $byCategory,
            'recent' => $recent,
            'year' => $year,
        ]);
    }

    /**
     * Total per bulan dalam satu query (bukan 12 query terpisah).
     *
     * @return array<int, array{month:int,label:string,total:int}>
     */
    private function monthlyTotals(Community $community, int $year): array
    {
        $monthExpr = match (DB::connection()->getDriverName()) {
            'sqlite' => "cast(strftime('%m', start_date) as integer)",
            'pgsql' => 'extract(month from start_date)::integer',
            default => 'month(start_date)',
        };

        $totals = Report::query()
            ->where('community', $community->value)
            ->whereYear('start_date', $year)
            ->groupBy(DB::raw($monthExpr))
            ->select(DB::raw("{$monthExpr} as m"), DB::raw('count(*) as total'))
            ->pluck('total', 'm');

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[] = [
                'month' => $m,
                'label' => $this->monthLabel($m),
                'total' => (int) ($totals[$m] ?? 0),
            ];
        }

        return $monthly;
    }

    /**
     * Aktivitas pelaporan + streak bulan berurutan.
     *
     * @return array{today:int,week:int,this_month:int,streak:int}
     */
    private function activity(Community $community): array
    {
        $base = fn () => Report::query()->where('community', $community->value);

        $today = $base()->whereDate('start_date', now()->toDateString())->count();
        $week = $base()->whereBetween('start_date', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString(),
        ])->count();
        $thisMonth = $base()
            ->whereYear('start_date', now()->year)
            ->whereMonth('start_date', now()->month)
            ->count();

        return [
            'today' => $today,
            'week' => $week,
            'this_month' => $thisMonth,
            'streak' => $this->streak($community),
        ];
    }

    /**
     * Jumlah bulan berurutan yang punya >= 1 laporan, dihitung mundur dari bulan
     * berjalan. Bulan berjalan tanpa laporan berarti streak 0.
     */
    private function streak(Community $community): int
    {
        $monthExpr = match (DB::connection()->getDriverName()) {
            'sqlite' => "cast(strftime('%Y%m', start_date) as integer)",
            'pgsql' => "cast(to_char(start_date, 'YYYYMM') as integer)",
            default => "cast(date_format(start_date, '%Y%m') as unsigned)",
        };

        $keys = Report::query()
            ->where('community', $community->value)
            ->groupBy(DB::raw($monthExpr))
            ->select(DB::raw($monthExpr.' as ym'))
            ->pluck('ym')
            ->map(fn ($v) => (int) $v)
            ->flip();

        $streak = 0;
        $cursor = now()->startOfMonth();
        while ($keys->has((int) $cursor->format('Ym'))) {
            $streak++;
            $cursor = $cursor->copy()->subMonthNoOverflow();
        }

        return $streak;
    }

    private function monthLabel(int $month): string
    {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][$month - 1];
    }
}
