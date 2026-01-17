<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Payment\Services\PaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentAdminController extends Controller
{
    public function __construct(private readonly PaymentService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'payment_method', 'search']);
        $transactions = $this->service->paginateTransactions($filters);

        return Inertia::render('Payment/Index', [
            'transactions' => $transactions,
            'filters'      => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $transaction = $this->service->findTransactionById($id);

        abort_if(! $transaction, 404);

        return Inertia::render('Payment/Show', [
            'transaction' => $transaction,
        ]);
    }

    public function refund(Request $request, int $orderId): RedirectResponse
    {
        $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->service->refund($orderId, $request->amount, $request->reason);

        return back()->with('success', 'Refund processed.');
    }
}
