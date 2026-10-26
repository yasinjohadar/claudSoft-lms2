<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * شكل موحّد لاستجابات API تطبيق الجوال: {success, message, data, meta?}.
 */
trait ApiResponse
{
    protected function success(mixed $data = null, ?string $message = null, ?array $meta = null, int $status = 200): JsonResponse
    {
        if ($data instanceof JsonResource) {
            $data = $data->resolve(request());
        }

        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function error(string $message, int $status = 400, ?array $errors = null, ?array $extra = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        if ($extra !== null) {
            $payload = array_merge($payload, $extra);
        }

        return response()->json($payload, $status);
    }

    /**
     * @return array{current_page:int,last_page:int,per_page:int,total:int}
     */
    protected function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
