<?php

declare(strict_types=1);

namespace App\Domain\Promotion\Services;

use App\Domain\Promotion\Models\Promotion;
use App\Domain\Promotion\Repositories\PromotionRepositoryInterface;
use App\Domain\Restaurant\Repositories\RestaurantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PromotionService
{
    public function __construct(
        private readonly PromotionRepositoryInterface $promotions,
        private readonly RestaurantRepositoryInterface $restaurants,
    ) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->promotions->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Promotion
    {
        return $this->promotions->findById($id);
    }

    public function findByIdWithRestaurant(int $id): ?Promotion
    {
        return $this->promotions->findByIdWithRestaurant($id);
    }

    public function findActiveByCode(string $code): ?Promotion
    {
        return $this->promotions->findActiveByCode($code);
    }

    public function create(array $data): Promotion
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        return $this->promotions->create($data);
    }

    public function update(int $id, array $data): Promotion
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        return $this->promotions->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->promotions->delete($id);
    }

    public function getRestaurantsList(): Collection
    {
        return $this->restaurants->allNamesList();
    }

    public function validateCode(string $code, float $orderTotal, ?int $restaurantId): array
    {
        $promotion = $this->promotions->findActiveByCode(strtoupper($code));

        if (! $promotion) {
            return ['valid' => false, 'message' => 'Invalid or expired promo code.'];
        }

        if ($promotion->isUsageLimitReached()) {
            return ['valid' => false, 'message' => 'Promo code usage limit reached.'];
        }

        if ($promotion->restaurant_id !== null && $restaurantId !== null) {
            if ($promotion->restaurant_id !== $restaurantId) {
                return ['valid' => false, 'message' => 'Promo code not valid for this restaurant.'];
            }
        }

        if ($promotion->minimum_order !== null && $orderTotal < (float) $promotion->minimum_order) {
            return [
                'valid'         => false,
                'message'       => "Minimum order of CHF {$promotion->minimum_order} required.",
                'minimum_order' => $promotion->minimum_order,
            ];
        }

        $discount = $promotion->calculateDiscount($orderTotal);

        return [
            'valid'     => true,
            'promotion' => $promotion,
            'discount'  => round($discount, 2),
            'new_total' => round($orderTotal - $discount, 2),
        ];
    }
}
