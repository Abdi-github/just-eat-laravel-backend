<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Promotion\Services\StampCardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StampCardController extends Controller
{
    public function __construct(private readonly StampCardService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'restaurant_id', 'active']);
        $stampCards = $this->service->paginate($filters);
        $restaurants = $this->service->getRestaurantsList();

        return Inertia::render('Promotion/StampIndex', [
            'stampCards'  => $stampCards,
            'restaurants' => $restaurants,
            'filters'     => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Promotion/StampCreate', [
            'restaurants' => $this->service->getRestaurantsList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'restaurant_id'     => ['required', 'integer', 'exists:restaurants,id'],
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'stamps_required'   => ['required', 'integer', 'min:1', 'max:100'],
            'reward_description'=> ['required', 'string', 'max:500'],
            'reward_type'       => ['required', 'in:PERCENTAGE,FLAT'],
            'reward_value'      => ['required', 'numeric', 'min:0'],
            'valid_from'        => ['nullable', 'date'],
            'valid_until'       => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active'         => ['boolean'],
        ]);

        $this->service->create($validated);

        return redirect()->route('admin.stamp-cards.index')->with('success', 'Stamp card created.');
    }

    public function show(int $id): Response
    {
        $stampCard = $this->service->findByIdWithRestaurant($id);

        abort_unless($stampCard, 404);

        return Inertia::render('Promotion/StampShow', [
            'stampCard'   => $stampCard,
            'restaurants' => $this->service->getRestaurantsList(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'restaurant_id'     => ['sometimes', 'integer', 'exists:restaurants,id'],
            'name'              => ['sometimes', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'stamps_required'   => ['sometimes', 'integer', 'min:1', 'max:100'],
            'reward_description'=> ['sometimes', 'string', 'max:500'],
            'reward_type'       => ['sometimes', 'in:PERCENTAGE,FLAT'],
            'reward_value'      => ['sometimes', 'numeric', 'min:0'],
            'valid_from'        => ['nullable', 'date'],
            'valid_until'       => ['nullable', 'date'],
            'is_active'         => ['boolean'],
        ]);

        $this->service->update($id, $validated);

        return back()->with('success', 'Stamp card updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('admin.stamp-cards.index')->with('success', 'Stamp card deleted.');
    }
}
