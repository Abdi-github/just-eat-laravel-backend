<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Delivery\Services\DeliveryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryController extends Controller
{
    public function __construct(private readonly DeliveryService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'search']);
        $deliveries = $this->service->paginate($filters);
        $couriers = $this->service->getActiveCouriers();

        return Inertia::render('Delivery/Index', [
            'deliveries' => $deliveries,
            'couriers'   => $couriers,
            'filters'    => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $delivery = $this->service->findByIdWithDetails($id);

        abort_if(! $delivery, 404);

        $couriers = $this->service->getActiveCouriers();

        return Inertia::render('Delivery/Show', [
            'delivery' => $delivery,
            'couriers' => $couriers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'order_id'     => ['required', 'integer', 'exists:orders,id'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $delivery = $this->service->createForOrder($request->only(['order_id', 'delivery_fee', 'notes']));

        return redirect()->route('admin.deliveries.show', $delivery->id)
            ->with('success', 'Delivery created.');
    }

    public function assignCourier(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'courier_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $this->service->assignCourier($id, (int) $request->courier_id);

        return back()->with('success', 'Courier assigned.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:PENDING,ASSIGNED,PICKED_UP,IN_TRANSIT,DELIVERED,CANCELLED,FAILED'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->updateStatus($id, $request->status, $request->reason);

        return back()->with('success', 'Status updated.');
    }
}
