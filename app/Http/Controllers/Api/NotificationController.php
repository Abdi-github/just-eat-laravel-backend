<?php

namespace App\Http\Controllers\Api;

use App\Domain\Notification\Services\NotificationService;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $service) {}

    /** GET /api/v1/notifications */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->getUserNotifications(
            $request->user()->id,
            $request->only(['type', 'is_read', 'limit']),
        );

        return ApiResponse::paginated($paginator, NotificationResource::collection($paginator->items()));
    }

    /** GET /api/v1/notifications/count */
    public function count(Request $request): JsonResponse
    {
        return ApiResponse::success($this->service->getCount($request->user()->id));
    }

    /** PATCH /api/v1/notifications/{id}/read */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = $this->service->findByIdForUser($id, $request->user()->id);

        if (! $notification) {
            return ApiResponse::error('Notification not found', 404);
        }

        $updated = $this->service->markAsRead($id, $request->user()->id);

        return ApiResponse::success(new NotificationResource($updated));
    }

    /** PATCH /api/v1/notifications/read-all */
    public function markAllAsRead(Request $request): JsonResponse
    {
        return ApiResponse::success($this->service->markAllAsRead($request->user()->id));
    }

    /** DELETE /api/v1/notifications/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = $this->service->findByIdForUser($id, $request->user()->id);

        if (! $notification) {
            return ApiResponse::error('Notification not found', 404);
        }

        $this->service->delete($id, $request->user()->id);

        return ApiResponse::success(null, 'Notification deleted');
    }

    /** DELETE /api/v1/notifications */
    public function destroyAll(Request $request): JsonResponse
    {
        return ApiResponse::success($this->service->deleteAll($request->user()->id));
    }
}
