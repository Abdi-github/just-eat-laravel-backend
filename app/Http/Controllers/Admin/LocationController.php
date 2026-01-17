<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Location\Services\LocationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function __construct(private readonly LocationService $service) {}
    // ── Cantons ───────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $cantonFilters = $request->only(['search']);
        $cityFilters = $request->only(['city_search', 'canton_id']);

        $cantons = $this->service->paginateCantons($cantonFilters);
        $cities = $this->service->paginateCities($cityFilters);
        $allCantons = $this->service->getAllCantons();

        return Inertia::render('Location/Index', [
            'cantons'     => $cantons,
            'cities'      => $cities,
            'allCantons'  => $allCantons,
            'filters'     => $request->only(['search', 'city_search', 'canton_id']),
        ]);
    }

    // ── Canton CRUD ───────────────────────────────────────────────────────────

    public function storeCanton(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'     => ['required', 'string', 'max:2', 'unique:cantons,code'],
            'name'     => ['required', 'array'],
            'name.fr'  => ['required', 'string', 'max:100'],
            'name.de'  => ['nullable', 'string', 'max:100'],
            'region'   => ['nullable', 'string', 'max:100'],
        ]);

        $this->service->createCanton($validated);

        return back()->with('success', 'Canton created.');
    }

    public function updateCanton(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'code'     => ['sometimes', 'string', 'max:2', 'unique:cantons,code,'.$id],
            'name'     => ['sometimes', 'array'],
            'name.fr'  => ['nullable', 'string', 'max:100'],
            'name.de'  => ['nullable', 'string', 'max:100'],
            'region'   => ['nullable', 'string', 'max:100'],
        ]);

        $this->service->updateCanton($id, $validated);

        return back()->with('success', 'Canton updated.');
    }

    public function destroyCanton(int $id): RedirectResponse
    {
        $result = $this->service->deleteCanton($id);

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        return back()->with('success', 'Canton deleted.');
    }

    // ── City CRUD ─────────────────────────────────────────────────────────────

    public function storeCity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'canton_id' => ['required', 'integer', 'exists:cantons,id'],
            'zip_code'  => ['required', 'string', 'max:10'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $this->service->createCity($validated);

        return back()->with('success', 'City created.');
    }

    public function updateCity(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'canton_id' => ['sometimes', 'integer', 'exists:cantons,id'],
            'zip_code'  => ['sometimes', 'string', 'max:10'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $this->service->updateCity($id, $validated);

        return back()->with('success', 'City updated.');
    }

    public function destroyCity(int $id): RedirectResponse
    {
        $result = $this->service->deleteCity($id);

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        return back()->with('success', 'City deleted.');
    }
}
