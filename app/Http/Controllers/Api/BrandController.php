<?php

namespace App\Http\Controllers\Api;

use App\Domain\Restaurant\Services\BrandService;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $service) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('limit', 20), 100);

        $filters = [
            'q' => $request->input('q'),
            'include_inactive' => $request->boolean('include_inactive'),
        ];

        $paginator  = $this->service->paginate($filters, $perPage);
        $collection = BrandResource::collection($paginator);

        return ApiResponse::paginated($paginator, $collection);
    }

    public function show(int $id): JsonResponse
    {
        $brand = $this->service->findByIdWithRestaurants($id);

        if (! $brand) {
            return ApiResponse::error('Brand not found.', 404);
        }

        return ApiResponse::success(new BrandResource($brand));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $brand = $this->service->findBySlugWithRestaurants($slug);

        if (! $brand) {
            return ApiResponse::error('Brand not found.', 404);
        }

        return ApiResponse::success(new BrandResource($brand));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'unique:brands,slug'],
            'description' => ['nullable', 'string'],
            'logo'        => ['nullable', 'string', 'url'],
            'website'     => ['nullable', 'string', 'url'],
            'is_active'   => ['boolean'],
        ]);

        $brand = $this->service->create($data);

        return ApiResponse::created(new BrandResource($brand), 'Brand created successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $brand = $this->service->findById($id);

        if (! $brand) {
            return ApiResponse::error('Brand not found.', 404);
        }

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'slug'        => ['sometimes', 'string', 'unique:brands,slug,' . $id],
            'description' => ['nullable', 'string'],
            'logo'        => ['nullable', 'string', 'url'],
            'website'     => ['nullable', 'string', 'url'],
            'is_active'   => ['boolean'],
        ]);

        $updated = $this->service->update($id, $data);

        return ApiResponse::success(new BrandResource($updated), 'Brand updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $brand = $this->service->findById($id);

        if (! $brand) {
            return ApiResponse::error('Brand not found.', 404);
        }

        $this->service->delete($id);

        return ApiResponse::success(null, 'Brand deleted successfully.');
    }
}
