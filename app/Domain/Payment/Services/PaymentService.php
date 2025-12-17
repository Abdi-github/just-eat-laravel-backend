<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Payment\Models\PaymentTransaction;
use App\Domain\Payment\Repositories\PaymentTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly PaymentTransactionRepositoryInterface $transactions,
    ) {}

    public function findOrderForPayment(int $orderId, int $userId): ?object
    {
        $order = $this->orders->findById($orderId);

        if (! $order || $order->user_id !== $userId) {
            return null;
        }

        return $order;
    }

    public function markOrderPaid(int $orderId): void
    {
        $this->orders->update($orderId, [
            'payment_status' => 'paid',
            'status'         => 'confirmed',
        ]);
    }

    public function markOrderPaymentFailed(int $orderId): void
    {
        $this->orders->update($orderId, [
            'payment_status' => 'failed',
        ]);
    }

    // Admin methods

    public function paginateTransactions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->transactions->paginate($filters, $perPage);
    }

    public function findTransactionById(int $id): ?PaymentTransaction
    {
        return $this->transactions->findByIdWithDetails($id);
    }

    public function refund(int $orderId, ?float $amount, string $reason): PaymentTransaction
    {
        $transaction = $this->transactions->findCompletedByOrderId($orderId);

        if (! $transaction) {
            throw new \RuntimeException('No completed transaction found for this order.');
        }

        $refundAmount = $amount ?? $transaction->amount;
        $status = $refundAmount < $transaction->amount ? 'PARTIAL_REFUND' : 'REFUNDED';

        return $this->transactions->update($transaction->id, [
            'status'        => $status,
            'refund_amount' => $refundAmount,
            'refund_reason' => $reason,
            'refunded_at'   => now(),
        ]);
    }
}
