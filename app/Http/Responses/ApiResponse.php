<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ApiResponse implements Responsable
{
    protected array $cookies = [];

    public function __construct(
        protected mixed $data = null,
        protected string $message = 'Success',
        protected int $status = 200,
    ) {
    }

    public function toResponse($request): JsonResponse
    {
        $response = response()->json([
            'success' => $this->status >= 200 && $this->status < 300,
            'message' => $this->message,
            'data' => $this->data,
        ], $this->status);

        return $response;
    }
}
