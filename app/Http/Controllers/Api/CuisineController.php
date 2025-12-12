<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Cuisine\StoreCuisineRequest;
use App\Http\Requests\Api\Cuisine\UpdateCuisineRequest;
use App\Http\Resources\CuisineResource;
use App\Http\Responses\ApiResponse;
use App\Domain\Cuisine\Services\CuisineService;
use Illuminate\Http\JsonResponse;

class CuisineController extends Controller
{
    public function __construct(private readonly CuisineService $service) {}

    public function index(): JsonResponse
    {
        $cuisines = $this->service->all();
        return ApiResponse::success(CuisineResource::collection($cuisines));
    }

    public function show(int $id): JsonResponse
    {
        $cuisine = $this->service->findById($id);

        if (! $cuisine) {
            return ApiResponse::error('Cuisine not found', 404);
        }

        return ApiResponse::success(new CuisineResource($cuisine));
    }

    public function store(StoreCuisineRequest $request): JsonResponse
    {
        $cuisine = $this->service->create($request->validated());
        return ApiResponse::created(new CuisineResource($cuisine));
    }

    public function update(UpdateCuisineRequest $request, int $id): JsonResponse
    {
        $cuisine = $this->service->update($id, $request->validated());
        return ApiResponse::success(new CuisineResource($cuisine));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return ApiResponse::success(null, 'Cuisine deleted');
    }
}
