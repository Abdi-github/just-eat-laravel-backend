<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Notification\Services\NotificationService;
use App\Domain\User\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $service,
        private readonly UserService $userService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Notification/Index');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'type'       => 'required|string|max:50',
            'title'      => 'required|string|max:200',
            'body'       => 'required|string|max:2000',
            'channel'    => 'nullable|in:IN_APP,EMAIL,BOTH',
            'priority'   => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
        ]);

        $this->service->sendToMultiple($validated['user_ids'], $validated);

        return back()->with('success', 'Notifications sent successfully.');
    }

    public function sendToAll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'     => 'required|string|max:50',
            'title'    => 'required|string|max:200',
            'body'     => 'required|string|max:2000',
            'channel'  => 'nullable|in:IN_APP,EMAIL,BOTH',
            'priority' => 'nullable|in:LOW,NORMAL,HIGH,URGENT',
        ]);

        $userIds = $this->userService->getActiveUserIds();
        $this->service->sendToMultiple($userIds, $validated);

        return back()->with('success', count($userIds) . ' notifications sent.');
    }
}
