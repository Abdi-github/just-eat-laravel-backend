<?php

namespace App\Http\Controllers\Api;

use App\Domain\Address\Services\AddressService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Address\StoreAddressRequest;
use App\Http\Requests\Api\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $service) {}

    public function index(Request $request): JsonResponse
    {
        $addresses = $this->service->getForUser($request->user()->id);

        return ApiResponse::success(AddressResource::collection($addresses));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $address = $this->service->findForUser($id, $request->user()->id);

        if (! $address) {
            return ApiResponse::error('Address not found.', 404);
        }

        return ApiResponse::success(new AddressResource($address));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->service->create($request->user()->id, $request->validated());

        return ApiResponse::created(new AddressResource($address->load(['city', 'canton'])));
    }

    public function update(UpdateAddressRequest $request, int $id): JsonResponse
    {
        $address = $this->service->update($id, $request->user()->id, $request->validated());

        if (! $address) {
            return ApiResponse::error('Address not found.', 404);
        }

        return ApiResponse::success(new AddressResource($address->load(['city', 'canton'])));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $this->service->delete($id, $request->user()->id);

        if (! $deleted) {
            return ApiResponse::error('Address not found.', 404);
        }

        return ApiResponse::success(null, 'Address deleted.');
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $address = $this->service->setDefault($id, $request->user()->id);

        if (! $address) {
            return ApiResponse::error('Address not found.', 404);
        }

        return ApiResponse::success(new AddressResource($address->load(['city', 'canton'])));
    }
}
