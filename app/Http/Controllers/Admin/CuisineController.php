<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Cuisine\Services\CuisineService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CuisineController extends Controller
{
    public function __construct(private readonly CuisineService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);
        $cuisines = $this->service->paginate($filters);

        return Inertia::render('Cuisine/Index', [
            'cuisines' => $cuisines,
            'filters'  => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $cuisine = $this->service->findByIdWithRestaurantCount($id);

        abort_unless($cuisine, 404);

        return Inertia::render('Cuisine/Show', [
            'cuisine' => $cuisine,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Cuisine/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'array'],
            'name.fr'    => ['required', 'string', 'max:100'],
            'name.de'    => ['nullable', 'string', 'max:100'],
            'name.en'    => ['nullable', 'string', 'max:100'],
            'is_active'  => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $validated['slug'] = Str::slug($validated['name']['en'] ?? $validated['name']['fr']);

        $this->service->create($validated);

        return redirect()->route('admin.cuisines.index')->with('success', 'Cuisine created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['array'],
            'name.fr'    => ['string', 'max:100'],
            'name.de'    => ['nullable', 'string', 'max:100'],
            'name.en'    => ['nullable', 'string', 'max:100'],
            'is_active'  => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $this->service->update($id, $validated);

        return back()->with('success', 'Cuisine updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('admin.cuisines.index')->with('success', 'Cuisine deleted.');
    }
}
