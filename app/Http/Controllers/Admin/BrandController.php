<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Restaurant\Services\BrandService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);
        $brands = $this->service->paginate($filters);

        return Inertia::render('Brand/Index', [
            'brands'  => $brands,
            'filters' => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $brand = $this->service->findByIdWithRestaurants($id);

        abort_unless($brand, 404);

        return Inertia::render('Brand/Show', [
            'brand' => $brand,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Brand/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'unique:brands,slug'],
            'description' => ['nullable', 'string'],
            'logo'        => ['nullable', 'string', 'url'],
            'website'     => ['nullable', 'string', 'url'],
            'is_active'   => ['boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $this->service->create($validated);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'slug'        => ['sometimes', 'string', 'unique:brands,slug,' . $id],
            'description' => ['nullable', 'string'],
            'logo'        => ['nullable', 'string', 'url'],
            'website'     => ['nullable', 'string', 'url'],
            'is_active'   => ['boolean'],
        ]);

        $this->service->update($id, $validated);

        return back()->with('success', 'Brand updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
