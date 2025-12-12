<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CantonResource;
use App\Http\Resources\CityResource;
use App\Http\Responses\ApiResponse;
use App\Domain\Location\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private readonly LocationService $service) {}

    public function cantons(): JsonResponse
    {
        return ApiResponse::success(CantonResource::collection($this->service->getAllCantons()));
    }

    public function cities(Request $request): JsonResponse
    {
        $filters = $request->only(['canton_id', 'search']);
        return ApiResponse::success(CityResource::collection($this->service->getAllCities($filters)));
    }

    public function searchCities(Request $request): JsonResponse
    {
        $filters = ['search' => $request->get('q', $request->get('search', ''))];
        return ApiResponse::success(CityResource::collection($this->service->getAllCities($filters)));
    }
}
