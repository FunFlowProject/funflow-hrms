<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class BusinessException extends Exception
{
    protected int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function __construct(
        string $message,
        int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}