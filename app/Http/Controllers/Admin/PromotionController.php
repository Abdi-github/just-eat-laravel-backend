<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Promotion\Services\PromotionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'restaurant_id']);
        $promotions = $this->service->paginate($filters);
        $restaurants = $this->service->getRestaurantsList();

        return Inertia::render('Promotion/Index', [
            'promotions'  => [
                'data' => $promotions->items(),
                'meta' => [
                    'current_page' => $promotions->currentPage(),
                    'last_page'    => $promotions->lastPage(),
                    'total'        => $promotions->total(),
                ],
            ],
            'restaurants' => $restaurants,
            'filters'     => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $promotion = $this->service->findByIdWithRestaurant($id);

        abort_unless($promotion, 404);

        return Inertia::render('Promotion/Show', [
            'promotion'   => $promotion,
            'restaurants' => $this->service->getRestaurantsList(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Promotion/Create', [
            'restaurants' => $this->service->getRestaurantsList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'          => ['required', 'string', 'max:50', 'unique:promotions,code'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'type'          => ['required', 'in:percentage,fixed'],
            'value'         => ['required', 'numeric', 'min:0'],
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'max_discount'  => ['nullable', 'numeric', 'min:0'],
            'usage_limit'   => ['nullable', 'integer', 'min:1'],
            'is_active'     => ['boolean'],
            'starts_at'     => ['nullable', 'date'],
            'expires_at'    => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->service->create($validated);

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'code'          => ['sometimes', 'string', 'max:50', "unique:promotions,code,{$id}"],
            'title'         => ['sometimes', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'type'          => ['sometimes', 'in:percentage,fixed'],
            'value'         => ['sometimes', 'numeric', 'min:0'],
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'max_discount'  => ['nullable', 'numeric', 'min:0'],
            'usage_limit'   => ['nullable', 'integer', 'min:1'],
            'is_active'     => ['boolean'],
            'starts_at'     => ['nullable', 'date'],
            'expires_at'    => ['nullable', 'date'],
        ]);

        $this->service->update($id, $validated);

        return back()->with('success', 'Promotion updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion deleted.');
    }
}
