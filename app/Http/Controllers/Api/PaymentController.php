<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Payment\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class PaymentController extends Controller
{
    private StripeClient $stripe;

    public function __construct(private readonly PaymentService $service)
    {
        $this->stripe = new StripeClient((string) config('services.stripe.secret'));
    }

    public function createIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = $this->service->findOrderForPayment($validated['order_id'], $request->user()->id);

        if (! $order) {
            return ApiResponse::error('Order not found or forbidden.', 404);
        }

        if ($order->payment_status === 'paid') {
            return ApiResponse::error('Order is already paid.', 422);
        }

        $amountCents = (int) round($order->total * 100);

        $intent = $this->stripe->paymentIntents->create([
            'amount'   => $amountCents,
            'currency' => 'chf',
            'metadata' => [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'user_id'      => $order->user_id,
            ],
        ]);

        return ApiResponse::success([
            'client_secret'     => $intent->client_secret,
            'payment_intent_id' => $intent->id,
            'amount'            => $order->total,
            'currency'          => 'chf',
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $secret    = (string) config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException) {
            return ApiResponse::error('Invalid webhook signature.', 400);
        }

        match ($event->type) {
            'payment_intent.succeeded'      => $this->handlePaymentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default                         => null,
        };

        return ApiResponse::success(null, 'Webhook received.');
    }

    private function handlePaymentSucceeded(object $paymentIntent): void
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if (! $orderId) {
            return;
        }

        $this->service->markOrderPaid((int) $orderId);
    }

    private function handlePaymentFailed(object $paymentIntent): void
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if (! $orderId) {
            return;
        }

        $this->service->markOrderPaymentFailed((int) $orderId);
    }
}
