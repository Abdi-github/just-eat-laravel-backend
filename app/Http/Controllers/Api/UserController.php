<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Domain\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->can('users.view')) {
            return ApiResponse::error('Forbidden', 403);
        }

        $filters = $request->only(['search', 'is_active']);
        $perPage = (int) $request->get('limit', 20);
        $paginator = $this->userService->paginate($filters, $perPage);

        return ApiResponse::paginated($paginator, UserResource::collection($paginator->items()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (! $user) {
            return ApiResponse::error('User not found', 404);
        }

        if ($request->user()->id !== $id && ! $request->user()->can('users.view')) {
            return ApiResponse::error('Forbidden', 403);
        }

        return ApiResponse::success(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (! $user) {
            return ApiResponse::error('User not found', 404);
        }

        if ($request->user()->id !== $id && ! $request->user()->can('users.update')) {
            return ApiResponse::error('Forbidden', 403);
        }

        $updated = $this->userService->update($id, $request->validated());

        return ApiResponse::success(new UserResource($updated));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->can('users.delete')) {
            return ApiResponse::error('Forbidden', 403);
        }

        $user = $this->userService->findById($id);

        if (! $user) {
            return ApiResponse::error('User not found', 404);
        }

        $this->userService->delete($id);

        return ApiResponse::success(null, 'User deleted');
    }
}
