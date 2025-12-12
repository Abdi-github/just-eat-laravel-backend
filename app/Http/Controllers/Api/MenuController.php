<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Menu\StoreMenuCategoryRequest;
use App\Http\Requests\Api\Menu\UpdateMenuCategoryRequest;
use App\Http\Requests\Api\Menu\StoreMenuItemRequest;
use App\Http\Requests\Api\Menu\UpdateMenuItemRequest;
use App\Http\Resources\MenuCategoryResource;
use App\Http\Resources\MenuItemResource;
use App\Http\Responses\ApiResponse;
use App\Domain\Menu\Services\MenuService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $menuService) {}

    // Menu Categories

    public function indexCategories(int $restaurantId): JsonResponse
    {
        $categories = $this->menuService->getCategories($restaurantId);

        return ApiResponse::success(MenuCategoryResource::collection($categories));
    }

    public function storeCategory(StoreMenuCategoryRequest $request, int $restaurantId): JsonResponse
    {
        $category = $this->menuService->createCategory($restaurantId, $request->validated());

        return ApiResponse::created(new MenuCategoryResource($category));
    }

    public function updateCategory(UpdateMenuCategoryRequest $request, int $restaurantId, int $catId): JsonResponse
    {
        $category = $this->menuService->updateCategory($restaurantId, $catId, $request->validated());

        if (! $category) {
            return ApiResponse::error('Category not found', 404);
        }

        return ApiResponse::success(new MenuCategoryResource($category));
    }

    public function destroyCategory(int $restaurantId, int $catId): JsonResponse
    {
        if (! $this->menuService->deleteCategory($restaurantId, $catId)) {
            return ApiResponse::error('Category not found', 404);
        }

        return ApiResponse::success(null, 'Category deleted');
    }

    // Menu Items

    public function indexItems(int $restaurantId): JsonResponse
    {
        $items = $this->menuService->getItems($restaurantId);

        return ApiResponse::success(MenuItemResource::collection($items));
    }

    public function storeItem(StoreMenuItemRequest $request, int $restaurantId): JsonResponse
    {
        $item = $this->menuService->createItem($restaurantId, $request->validated());

        return ApiResponse::created(new MenuItemResource($item->load('category')));
    }

    public function updateItem(UpdateMenuItemRequest $request, int $restaurantId, int $itemId): JsonResponse
    {
        $item = $this->menuService->updateItem($restaurantId, $itemId, $request->validated());

        if (! $item) {
            return ApiResponse::error('Menu item not found', 404);
        }

        return ApiResponse::success(new MenuItemResource($item));
    }

    public function destroyItem(int $restaurantId, int $itemId): JsonResponse
    {
        if (! $this->menuService->deleteItem($restaurantId, $itemId)) {
            return ApiResponse::error('Menu item not found', 404);
        }

        return ApiResponse::success(null, 'Menu item deleted');
    }
}
