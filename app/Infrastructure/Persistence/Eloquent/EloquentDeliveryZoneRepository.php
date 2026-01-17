<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Delivery\Models\DeliveryZone;
use App\Domain\Delivery\Repositories\DeliveryZoneRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentDeliveryZoneRepository implements DeliveryZoneRepositoryInterface
{
    public function __construct(private DeliveryZone $model) {}

    public function findByRestaurant(int $restaurantId): Collection
    {
        return $this->model->where('restaurant_id', $restaurantId)->get();
    }
}
