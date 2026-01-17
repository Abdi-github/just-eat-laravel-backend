<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Restaurant\Models\OpeningHour;
use App\Domain\Restaurant\Repositories\OpeningHourRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentOpeningHourRepository implements OpeningHourRepositoryInterface
{
    public function __construct(private OpeningHour $model) {}

    public function findByRestaurant(int $restaurantId): Collection
    {
        return $this->model->where('restaurant_id', $restaurantId)
            ->orderBy('day_of_week')
            ->get();
    }
}
