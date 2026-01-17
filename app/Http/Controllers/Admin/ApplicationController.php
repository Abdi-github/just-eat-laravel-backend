<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\User\Services\ApplicationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationService $service) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'type', 'search']);
        $applications = $this->service->paginate($filters);

        return Inertia::render('Application/Index', [
            'applications' => $applications,
            'filters'      => $filters,
        ]);
    }

    public function approve(int $userId): RedirectResponse
    {
        $this->service->approve($userId);

        return back()->with('success', 'Application approved.');
    }

    public function reject(Request $request, int $userId): RedirectResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->reject($userId, $request->get('reason'));

        return back()->with('success', 'Application rejected.');
    }
}
