<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

abstract class Controller
{
    protected function apiResponse(mixed $data = null, string $message = 'Success', int $status = 200): ApiResponse
    {
        return new ApiResponse($data, $message, $status);
    }

    protected function reportError(Throwable $e, string $userFriendlyMessage = 'An internal error occurred'): ApiResponse
    {
        $traceId = (string) Str::uuid();

        Log::error("Application Error: {$e->getMessage()}", [
            'trace_id' => $traceId,
            'user_id' => Auth::id() ?? 'guest',
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);

        return $this->apiResponse(
            data: ['trace_id' => $traceId],
            message: "{$userFriendlyMessage} (Trace ID: {$traceId})",
            status: 500,
        );
    }

    protected function logAction(string $description, mixed $model = null, array $metadata = []): void
    {
        activity()
            ->performedOn($model)
            ->causedBy(Auth::user())
            ->withProperties(array_merge($metadata, [
                'ip' => request()->ip(),
            ]))
            ->log($description);
    }
}
