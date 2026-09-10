<?php

namespace App\Http\Controllers;

use App\Enums\Community;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Category;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    private function community(Request $request): Community
    {
        return $request->attributes->get('community');
    }

    public function index(Request $request): Response
    {
        $community = $this->community($request);
        $filters = $request->only(['month', 'year', 'category_id', 'q']);

        $reports = Report::filtered($community, $filters)
            ->with(['category:id,name', 'user:id,name'])
            ->withCount('photos')
            ->paginate(Report::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Reports/Index', [
            'title' => 'Laporan',
            'community' => $community->config(),
            'reports' => $reports,
            'filters' => $filters,
            'categories' => Category::optionsFor($community),
        ]);
    }

    public function create(Request $request): Response
    {
        $community = $this->community($request);

        return Inertia::render('Reports/Form', [
            'title' => 'Tambah Laporan',
            'community' => $community->config(),
            'report' => null,
            'categories' => Category::optionsFor($community),
        ]);
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $community = $this->community($request);
        $data = $request->validated();

        $report = new Report($data);
        $report->community = $community;
        $report->user_id = $request->user()->id;
        $report->save();

        $this->syncPhotos($report, $data['photos'] ?? null);

        return redirect()
            ->route('community.reports.show', ['community' => $community->value, 'report' => $report])
            ->with('success', 'Laporan tersimpan.');
    }

    public function show(Request $request, string $communityParam, int $report): Response
    {
        $community = $this->community($request);

        $r = Report::filtered($community)
            ->with(['user:id,name', 'category:id,name', 'photos'])
            ->findOrFail($report);

        return Inertia::render('Reports/Show', [
            'title' => $r->title,
            'community' => $community->config(),
            'report' => [
                'id' => $r->id,
                'title' => $r->title,
                'description' => $r->description,
                'start_date' => $r->start_date->format('Y-m-d'),
                'end_date' => $r->end_date?->format('Y-m-d'),
                'location' => $r->location,
                'category' => ['id' => $r->category->id, 'name' => $r->category->name],
                'user' => ['id' => $r->user->id, 'name' => $r->user->name],
                'photos' => $r->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'url' => asset('storage/'.$p->path),
                    'original_name' => $p->original_name,
                ])->all(),
            ],
            'editable' => $request->user()->can('update', $r),
        ]);
    }

    public function edit(Request $request, string $communityParam, int $report): Response
    {
        $community = $this->community($request);

        $r = Report::filtered($community)->with('photos')->findOrFail($report);
        $this->authorize('update', $r);

        return Inertia::render('Reports/Form', [
            'title' => 'Ubah Laporan',
            'community' => $community->config(),
            'report' => [
                'id' => $r->id,
                'title' => $r->title,
                'description' => $r->description,
                'start_date' => $r->start_date->format('Y-m-d'),
                'end_date' => $r->end_date?->format('Y-m-d'),
                'location' => $r->location,
                'category_id' => $r->category_id,
                'photos' => $r->photos->map(fn ($p) => [
                    'id' => $p->id,
                    'url' => asset('storage/'.$p->path),
                    'original_name' => $p->original_name,
                ])->all(),
            ],
            'categories' => Category::optionsFor($community),
        ]);
    }

    public function update(UpdateReportRequest $request, string $communityParam, int $report): RedirectResponse
    {
        $community = $this->community($request);

        $r = Report::filtered($community)->findOrFail($report);
        $this->authorize('update', $r);

        $data = $request->validated();
        $r->update($data);

        foreach ($data['remove_photo_ids'] ?? [] as $photoId) {
            $photo = $r->photos()->whereKey($photoId)->first();
            if ($photo) {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
            }
        }

        $this->syncPhotos($r, $data['photos'] ?? null);

        return redirect()
            ->route('community.reports.show', ['community' => $community->value, 'report' => $r])
            ->with('success', 'Laporan diperbarui.');
    }

    public function destroy(Request $request, string $communityParam, int $report): RedirectResponse
    {
        $community = $this->community($request);

        $r = Report::filtered($community)->findOrFail($report);
        $this->authorize('delete', $r);

        foreach ($r->photos()->pluck('path')->all() as $path) {
            Storage::disk('public')->delete($path);
        }
        $r->delete();

        return redirect('/'.$community->value.'/reports')
            ->with('success', 'Laporan dihapus.');
    }

    public function export(Request $request)
    {
        $community = $this->community($request);
        $filters = $request->only(['month', 'year', 'category_id', 'q']);

        $filename = sprintf(
            'laporan-%s-%s.xlsx',
            $community->value,
            ! empty($filters['month']) && ! empty($filters['year'])
                ? $filters['year'].'-'.str_pad((string) $filters['month'], 2, '0', STR_PAD_LEFT)
                : date('Y-m-d')
        );

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReportExport($community, $filters),
            $filename
        );
    }

    private function syncPhotos(Report $report, ?array $files): void
    {
        foreach ($files ?? [] as $file) {
            $name = Str::uuid().'.'.$file->extension();
            $relative = "reports/{$report->community->value}/{$report->id}/{$name}";
            $file->storeAs("reports/{$report->community->value}/{$report->id}", $name, 'public');
            $report->photos()->create([
                'path' => $relative,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }
}
