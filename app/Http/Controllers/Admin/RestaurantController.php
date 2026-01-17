<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Location\Services\LocationService;
use App\Domain\Restaurant\Services\RestaurantService;
use App\Domain\User\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantController extends Controller
{
    public function __construct(
        private readonly RestaurantService $restaurantService,
        private readonly LocationService $locationService,
        private readonly UserService $userService,
    ) {}

    public function pending(Request $request): Response
    {
        $restaurants = $this->restaurantService->paginatePending(20);

        return Inertia::render('Restaurant/Pending', [
            'restaurants' => $restaurants->withQueryString(),
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $this->restaurantService->approve($id);

        return back()->with('success', 'Restaurant approved.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $this->restaurantService->reject($id);

        return redirect()->route('admin.restaurants.pending')->with('success', 'Restaurant rejected and removed.');
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $restaurants = $this->restaurantService->paginateForAdmin($filters, 20);

        return Inertia::render('Restaurant/Index', [
            'restaurants' => $restaurants->withQueryString(),
            'filters'     => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $restaurant = $this->restaurantService->findWithFullDetails($id);

        abort_unless($restaurant, 404);

        return Inertia::render('Restaurant/Show', [
            'restaurant' => $restaurant,
        ]);
    }

    public function create(): Response
    {
        $cantons = $this->locationService->getAllCantons();
        $cities  = $this->locationService->getAllCities();
        $users   = $this->userService->paginate(['is_active' => true], 1000);

        return Inertia::render('Restaurant/Create', [
            'cantons' => $cantons->map(fn ($c) => ['id' => $c->id, 'code' => $c->code, 'name' => $c->name]),
            'cities'  => $cities->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'canton_id' => $c->canton_id, 'zip_code' => $c->zip_code]),
            'users'   => $users->items(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'phone'                   => ['nullable', 'string', 'max:30'],
            'email'                   => ['nullable', 'email', 'max:255'],
            'website'                 => ['nullable', 'url', 'max:255'],
            'price_range'             => ['required', 'in:budget,moderate,upscale,fine_dining'],
            'minimum_order'           => ['nullable', 'numeric', 'min:0'],
            'delivery_fee'            => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['nullable', 'integer', 'min:0'],
            'accepts_pickup'          => ['boolean'],
            'accepts_delivery'        => ['boolean'],
            'user_id'                 => ['nullable', 'exists:users,id'],
            'canton_id'               => ['required', 'exists:cantons,id'],
            'city_id'                 => ['required', 'exists:cities,id'],
            'street'                  => ['required', 'string', 'max:255'],
            'zip_code'                => ['required', 'string', 'max:20'],
        ]);

        $restaurant = $this->restaurantService->storeWithAddress($validated);

        return redirect()
            ->route('admin.restaurants.show', $restaurant->id)
            ->with('success', 'Restaurant created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'is_active'   => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $this->restaurantService->update($id, $validated);

        return back()->with('success', 'Restaurant updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->restaurantService->delete($id);

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant deleted.');
    }
}
