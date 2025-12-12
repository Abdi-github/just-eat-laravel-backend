<?php

namespace App\Domain\Restaurant\Repositories;

use Illuminate\Support\Collection;

interface OpeningHourRepositoryInterface
{
    public function findByRestaurant(int $restaurantId): Collection;
}
