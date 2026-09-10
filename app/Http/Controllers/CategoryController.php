<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    private function community(Request $request): \App\Enums\Community
    {
        return $request->attributes->get('community');
    }

    public function index(Request $request): Response
    {
        $community = $this->community($request);

        $categories = Category::for($community)
            ->withCount('reports')
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Index', [
            'title' => 'Kategori',
            'community' => $community->config(),
            'categories' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $community = $this->community($request);
        $category = new Category(['name' => $request->validated()['name']]);
        $category->community = $community;
        $category->save();

        return redirect('/'.$community->value.'/categories')
            ->with('success', 'Kategori ditambahkan.');
    }

    public function update(StoreCategoryRequest $request, string $communityParam, int $category): RedirectResponse
    {
        $community = $this->community($request);
        $cat = Category::for($community)->findOrFail($category);
        $cat->update(['name' => $request->validated()['name']]);

        return redirect('/'.$community->value.'/categories')
            ->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Request $request, string $communityParam, int $category): RedirectResponse
    {
        $community = $this->community($request);
        $cat = Category::for($community)->findOrFail($category);

        $inUse = Report::query()->where('category_id', $cat->id)->exists();
        if ($inUse) {
            return redirect('/'.$community->value.'/categories')
                ->with('error', 'Kategori masih dipakai laporan, tidak bisa dihapus.');
        }

        $cat->delete();

        return redirect('/'.$community->value.'/categories')
            ->with('success', 'Kategori dihapus.');
    }
}