<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Payment\Models\PaymentTransaction;
use App\Domain\Payment\Repositories\PaymentTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPaymentTransactionRepository implements PaymentTransactionRepositoryInterface
{
    public function __construct(private PaymentTransaction $model) {}

    public function findById(int $id): ?PaymentTransaction
    {
        return $this->model->find($id);
    }

    public function findByIdWithDetails(int $id): ?PaymentTransaction
    {
        return $this->model->with(['order.user', 'user'])->find($id);
    }

    public function findCompletedByOrderId(int $orderId): ?PaymentTransaction
    {
        return $this->model
            ->where('order_id', $orderId)
            ->where('status', 'COMPLETED')
            ->first();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['order', 'user']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('stripe_payment_intent_id', 'like', "%{$search}%")
                  ->orWhere('provider_transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): PaymentTransaction
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): PaymentTransaction
    {
        $transaction = $this->model->findOrFail($id);
        $transaction->update($data);
        return $transaction->fresh();
    }
}
