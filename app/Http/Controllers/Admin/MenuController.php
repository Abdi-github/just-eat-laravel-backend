<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Menu\Services\MenuService;
use App\Domain\Restaurant\Services\RestaurantService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menuService,
        private readonly RestaurantService $restaurantService,
    ) {}

    public function index(int $restaurantId): Response
    {
        $restaurant = $this->restaurantService->findWithFullDetails($restaurantId);

        abort_if(! $restaurant, 404);

        return Inertia::render('Menu/Index', [
            'restaurant' => $restaurant,
        ]);
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function storeCategory(Request $request, int $restaurantId): RedirectResponse
    {
        abort_if(! $this->restaurantService->findById($restaurantId), 404);

        $validated = $request->validate([
            'name'        => ['required', 'array'],
            'name.fr'     => ['required', 'string', 'max:100'],
            'name.de'     => ['nullable', 'string', 'max:100'],
            'name.en'     => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'array'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $this->menuService->createCategory($restaurantId, $validated);

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, int $restaurantId, int $catId): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'array'],
            'name.fr'     => ['sometimes', 'string', 'max:100'],
            'name.de'     => ['nullable', 'string', 'max:100'],
            'name.en'     => ['nullable', 'string', 'max:100'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $this->menuService->updateCategory($restaurantId, $catId, $validated);

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(int $restaurantId, int $catId): RedirectResponse
    {
        $this->menuService->deleteCategory($restaurantId, $catId);

        return back()->with('success', 'Category deleted.');
    }

    // ── Items ─────────────────────────────────────────────────────────────────

    public function storeItem(Request $request, int $restaurantId): RedirectResponse
    {
        abort_if(! $this->restaurantService->findById($restaurantId), 404);

        $validated = $request->validate([
            'menu_category_id'   => ['required', 'integer', 'exists:menu_categories,id'],
            'name'               => ['required', 'array'],
            'name.fr'            => ['required', 'string', 'max:150'],
            'name.de'            => ['nullable', 'string', 'max:150'],
            'name.en'            => ['nullable', 'string', 'max:150'],
            'description'        => ['nullable', 'array'],
            'price'              => ['required', 'numeric', 'min:0'],
            'image'              => ['nullable', 'url', 'max:500'],
            'is_available'       => ['boolean'],
            'is_featured'        => ['boolean'],
            'preparation_time'   => ['nullable', 'integer', 'min:0'],
        ]);

        $this->menuService->createItem($restaurantId, $validated);

        return back()->with('success', 'Menu item created.');
    }

    public function updateItem(Request $request, int $restaurantId, int $itemId): RedirectResponse
    {
        $validated = $request->validate([
            'menu_category_id' => ['sometimes', 'integer', 'exists:menu_categories,id'],
            'name'             => ['sometimes', 'array'],
            'name.fr'          => ['sometimes', 'string', 'max:150'],
            'name.de'          => ['nullable', 'string', 'max:150'],
            'name.en'          => ['nullable', 'string', 'max:150'],
            'description'      => ['nullable', 'array'],
            'price'            => ['sometimes', 'numeric', 'min:0'],
            'image'            => ['nullable', 'url', 'max:500'],
            'is_available'     => ['boolean'],
            'is_featured'      => ['boolean'],
            'preparation_time' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->menuService->updateItem($restaurantId, $itemId, $validated);

        return back()->with('success', 'Menu item updated.');
    }

    public function destroyItem(int $restaurantId, int $itemId): RedirectResponse
    {
        $this->menuService->deleteItem($restaurantId, $itemId);

        return back()->with('success', 'Menu item deleted.');
    }
}
