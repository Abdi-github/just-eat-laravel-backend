<?php

declare(strict_types=1);

namespace App\Domain\Restaurant\Services;

use App\Domain\Cuisine\Repositories\CuisineRepositoryInterface;
use App\Domain\Menu\Repositories\MenuItemRepositoryInterface;
use App\Domain\Restaurant\Repositories\RestaurantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchService
{
    public function __construct(
        private readonly RestaurantRepositoryInterface $restaurants,
        private readonly MenuItemRepositoryInterface $menuItems,
        private readonly CuisineRepositoryInterface $cuisines,
    ) {}

    public function searchRestaurants(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->restaurants->searchRestaurants($filters, $perPage);
    }

    public function searchMenuItems(int $restaurantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->menuItems->searchByRestaurant($restaurantId, $filters, $perPage);
    }

    public function getSuggestions(string $query, int $limit = 5): array
    {
        $restaurants = $this->restaurants->searchRestaurants(['q' => $query, 'limit' => $limit], $limit);
        $cuisines = $this->cuisines->all()->filter(function ($c) use ($query) {
            $name = $c->getTranslations('name');
            foreach ($name as $translation) {
                if (stripos($translation, $query) !== false) {
                    return true;
                }
            }
            return false;
        })->take($limit)->values();

        return [
            'restaurants' => $restaurants->getCollection()->map(fn ($r) => [
                'id' => $r->id, 'name' => $r->name, 'slug' => $r->slug, 'logo' => $r->logo, 'type' => 'restaurant',
            ]),
            'cuisines' => $cuisines->map(fn ($c) => [
                'id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'icon' => $c->icon, 'type' => 'cuisine',
            ]),
        ];
    }

    public function findActiveRestaurant(int $id)
    {
        return $this->restaurants->findActiveById($id);
    }
}
