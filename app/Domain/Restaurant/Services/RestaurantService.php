<?php

namespace App\Domain\Restaurant\Services;

use App\Domain\Address\Repositories\AddressRepositoryInterface;
use App\Domain\Delivery\Repositories\DeliveryZoneRepositoryInterface;
use App\Domain\Menu\Repositories\MenuCategoryRepositoryInterface;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Restaurant\Repositories\OpeningHourRepositoryInterface;
use App\Domain\Restaurant\Repositories\RestaurantRepositoryInterface;
use App\Domain\Review\Repositories\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RestaurantService
{
    public function __construct(
        private readonly RestaurantRepositoryInterface $restaurants,
        private readonly ReviewRepositoryInterface $reviews,
        private readonly DeliveryZoneRepositoryInterface $deliveryZones,
        private readonly OpeningHourRepositoryInterface $openingHours,
        private readonly MenuCategoryRepositoryInterface $menuCategories,
        private readonly AddressRepositoryInterface $addresses,
    ) {}

    public function paginate(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return $this->restaurants->paginate($filters, $perPage, $page);
    }

    public function findById(int $id): ?Restaurant
    {
        return $this->restaurants->findById($id);
    }

    public function findBySlug(string $slug): ?Restaurant
    {
        return $this->restaurants->findBySlug($slug);
    }

    public function create(array $data): Restaurant
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->restaurants->create($data);
    }

    public function update(int $id, array $data): Restaurant
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->restaurants->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->restaurants->delete($id);
    }

    public function getMenu(int $restaurantId): Collection
    {
        return $this->menuCategories->getWithAvailableItems($restaurantId);
    }

    public function getReviews(int $restaurantId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->reviews->paginateByRestaurant($restaurantId, $perPage);
    }

    public function getDeliveryZones(int $restaurantId): Collection
    {
        return $this->deliveryZones->findByRestaurant($restaurantId);
    }

    public function getOpeningHours(int $restaurantId): Collection
    {
        return $this->openingHours->findByRestaurant($restaurantId);
    }

    // Admin-specific methods

    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->restaurants->paginateForAdmin($filters, $perPage);
    }

    public function paginatePending(int $perPage = 20): LengthAwarePaginator
    {
        return $this->restaurants->paginatePending($perPage);
    }

    public function findWithFullDetails(int $id): ?Restaurant
    {
        return $this->restaurants->findWithFullDetails($id);
    }

    public function approve(int $id): Restaurant
    {
        return $this->restaurants->update($id, ['is_active' => true]);
    }

    public function reject(int $id): bool
    {
        return $this->restaurants->delete($id);
    }

    public function storeWithAddress(array $validated): Restaurant
    {
        $address = $this->addresses->create([
            'street'    => $validated['street'],
            'zip_code'  => $validated['zip_code'],
            'city_id'   => $validated['city_id'],
            'canton_id' => $validated['canton_id'],
        ]);

        return $this->restaurants->create([
            'name'                    => $validated['name'],
            'slug'                    => Str::slug($validated['name']) . '-' . Str::random(6),
            'description'             => $validated['description'] ?? null,
            'phone'                   => $validated['phone'] ?? null,
            'email'                   => $validated['email'] ?? null,
            'website'                 => $validated['website'] ?? null,
            'price_range'             => $validated['price_range'],
            'minimum_order'           => $validated['minimum_order'] ?? 0,
            'delivery_fee'            => $validated['delivery_fee'] ?? 0,
            'estimated_delivery_time' => $validated['estimated_delivery_time'] ?? 30,
            'accepts_pickup'          => $validated['accepts_pickup'] ?? true,
            'accepts_delivery'        => $validated['accepts_delivery'] ?? true,
            'user_id'                 => $validated['user_id'] ?? null,
            'address_id'              => $address->id,
            'is_active'               => true,
            'is_featured'             => false,
            'average_rating'          => 0,
            'total_reviews'           => 0,
        ]);
    }
}
