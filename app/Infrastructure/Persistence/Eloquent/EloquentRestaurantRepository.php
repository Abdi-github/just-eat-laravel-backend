<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Restaurant\Repositories\RestaurantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentRestaurantRepository implements RestaurantRepositoryInterface
{
    public function __construct(private Restaurant $model) {}

    public function findById(int $id): ?Restaurant
    {
        return $this->model->with(['brand', 'address.city', 'address.canton', 'cuisines', 'owner'])->find($id);
    }

    public function findBySlug(string $slug): ?Restaurant
    {
        return $this->model->with(['brand', 'address.city', 'address.canton', 'cuisines', 'owner'])
            ->where('slug', $slug)->first();
    }

    public function findActiveById(int $id): ?Restaurant
    {
        return $this->model->active()->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = $this->model->with(['brand', 'address.city', 'cuisines']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['cuisine_id'])) {
            $query->whereHas('cuisines', fn($q) => $q->where('cuisines.id', $filters['cuisine_id']));
        }

        if (!empty($filters['city_id'])) {
            $query->whereHas('address', fn($q) => $q->where('city_id', $filters['city_id']));
        }

        if (!empty($filters['canton_id'])) {
            $query->whereHas('address', fn($q) => $q->where('canton_id', $filters['canton_id']));
        }

        if (!empty($filters['price_range'])) {
            $query->where('price_range', $filters['price_range']);
        }

        if (!empty($filters['is_featured'])) {
            $query->where('is_featured', true);
        }

        return $query->orderByDesc('is_featured')->orderByDesc('average_rating')->paginate($perPage, ['*'], 'page', $page);
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['address.city', 'cuisines', 'brand'])
            ->withCount(['orders', 'reviews']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function paginatePending(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with(['address.city', 'owner'])
            ->where('is_active', false)
            ->whereDoesntHave('orders')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findWithFullDetails(int $id): ?Restaurant
    {
        return $this->model->with([
            'address.city.canton',
            'cuisines',
            'brand',
            'owner',
            'menuCategories.items',
            'deliveryZones',
            'openingHours',
        ])->withCount(['orders', 'reviews'])->find($id);
    }

    public function searchRestaurants(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['address.city', 'brand', 'cuisines'])->active();

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['cuisine_id'])) {
            $query->whereHas('cuisines', fn ($sub) => $sub->where('cuisines.id', $filters['cuisine_id']));
        }

        if (! empty($filters['city_id'])) {
            $query->whereHas('address', fn ($sub) => $sub->where('city_id', $filters['city_id']));
        }

        if (! empty($filters['canton_id'])) {
            $query->whereHas('address', fn ($sub) => $sub->where('canton_id', $filters['canton_id']));
        }

        if (! empty($filters['price_range'])) {
            $query->where('price_range', $filters['price_range']);
        }

        if (! empty($filters['accepts_delivery'])) {
            $query->where('accepts_delivery', true);
        }

        if (! empty($filters['accepts_pickup'])) {
            $query->where('accepts_pickup', true);
        }

        if (! empty($filters['is_featured'])) {
            $query->featured();
        }

        $allowedSorts = ['name', 'average_rating', 'delivery_fee', 'minimum_order', 'created_at'];
        $sortBy = in_array($filters['sort_by'] ?? null, $allowedSorts, true) ? $filters['sort_by'] : 'name';
        $sortDir = ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function topByRevenue(int $limit = 10): Collection
    {
        return $this->model
            ->withCount('orders')
            ->withSum(['orders' => fn ($q) => $q->where('payment_status', 'paid')], 'total')
            ->orderByDesc('orders_sum_total')
            ->limit($limit)
            ->get();
    }

    public function allNamesList(): Collection
    {
        return $this->model->orderBy('name')->get(['id', 'name']);
    }

    public function create(array $data): Restaurant
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Restaurant
    {
        $restaurant = $this->model->findOrFail($id);
        $restaurant->update($data);
        return $restaurant->fresh(['brand', 'address.city', 'cuisines']);
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
