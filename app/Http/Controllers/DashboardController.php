<?php

namespace App\Http\Controllers;

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

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[] = [
                'month' => $m,
                'label' => $this->monthLabel($m),
                'total' => Report::filtered($community, ['year' => $year, 'month' => $m])->count(),
            ];
        }

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
            ])->all();

        return Inertia::render('Dashboard', [
            'title' => 'Dashboard',
            'community' => $community->config(),
            'stats' => [
                'total_reports' => $totalReports,
                'total_members' => $totalMembers,
                'this_month' => $thisMonth,
                'this_year' => $thisYear,
            ],
            'monthly' => $monthly,
            'byCategory' => $byCategory,
            'recent' => $recent,
            'year' => $year,
        ]);
    }

    private function monthLabel(int $month): string
    {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][$month - 1];
    }
}
