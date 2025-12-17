<?php

namespace App\Domain\Delivery\Repositories;

use Illuminate\Support\Collection;

interface DeliveryZoneRepositoryInterface
{
    public function findByRestaurant(int $restaurantId): Collection;
}
