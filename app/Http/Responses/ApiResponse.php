<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public static function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], 201);
    }

    public static function error(string $message, int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public static function paginated(LengthAwarePaginator $paginator, mixed $collection): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'message'    => 'Success',
            'data'       => $collection,
            'pagination' => [
                'page'       => $paginator->currentPage(),
                'limit'      => $paginator->perPage(),
                'total'      => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
            ],
        ]);
    }
}
