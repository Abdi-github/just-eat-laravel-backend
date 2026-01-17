<?php

declare(strict_types=1);

namespace App\Domain\Review\Services;

use App\Domain\Review\Models\Review;
use App\Domain\Review\Repositories\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    public function __construct(private readonly ReviewRepositoryInterface $reviews) {}

    public function paginateAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->reviews->paginateAll($filters, $perPage);
    }

    public function findById(int $id): ?Review
    {
        return $this->reviews->findById($id);
    }

    public function findByIdWithDetails(int $id): ?Review
    {
        return $this->reviews->findByIdWithDetails($id);
    }

    public function create(array $data): Review
    {
        return $this->reviews->create($data);
    }

    public function update(int $id, array $data): Review
    {
        return $this->reviews->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->reviews->delete($id);
    }
}
