<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Order\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $paginator = $this->orderService->paginateAll($filters, 20);

        return Inertia::render('Order/Index', [
            'orders'  => $paginator->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $order = $this->orderService->findByIdWithReview($id);

        abort_unless($order, 404);

        return Inertia::render('Order/Show', [
            'order' => $order,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,preparing,picked_up,delivered,cancelled'],
        ]);

        $this->orderService->updateStatus($id, $validated['status']);

        return back()->with('success', 'Order status updated.');
    }
}
