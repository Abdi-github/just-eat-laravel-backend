<?php

namespace App\Domain\Payment\Repositories;

use App\Domain\Payment\Models\PaymentTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentTransactionRepositoryInterface
{
    public function findById(int $id): ?PaymentTransaction;
    public function findByIdWithDetails(int $id): ?PaymentTransaction;
    public function findCompletedByOrderId(int $orderId): ?PaymentTransaction;
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): PaymentTransaction;
    public function update(int $id, array $data): PaymentTransaction;
}
