<?php

declare(strict_types=1);

namespace App\Domain\Payment\Models;

use App\Domain\Order\Models\Order;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'order_id',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'provider_name',
        'provider_transaction_id',
        'status',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'refund_amount',
        'refund_reason',
        'refund_id',
        'refunded_at',
        'error_message',
        'error_code',
        'attempts',
        'ip_address',
    ];

    protected $hidden = [
        'stripe_client_secret',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'float',
            'refund_amount'=> 'float',
            'refunded_at'  => 'datetime',
            'attempts'     => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
