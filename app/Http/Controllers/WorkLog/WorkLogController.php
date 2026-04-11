<?php

namespace App\Http\Controllers\WorkLog;

use App\Http\Controllers\Controller;
use App\Services\WorkLog\WorkLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkLogController extends Controller
{
    public function __construct(
        private readonly WorkLogService $workLogService
    ) {
    }

    public function index(): View
    {
        return view('work-logs.index');
    }

    public function myIndex(): View
    {
        return view('work-logs.my-index');
    }

    public function datatable(): JsonResponse
    {
        return $this->workLogService->datatable();
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tasks' => 'required|array|min:1',
            'tasks.*.name' => 'required|string|max:255',
            'tasks.*.duration_minutes' => 'required|integer|min:1',
            'tasks.*.done' => 'boolean',
        ]);

        $workLog = $this->workLogService->store($data);

        return response()->json([
            'message' => __('Work log saved successfully.'),
            'data' => $workLog,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $workLog = $this->workLogService->show($id);
        return response()->json(['data' => $workLog]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'tasks' => 'required|array|min:1',
            'tasks.*.name' => 'required|string|max:255',
            'tasks.*.duration_minutes' => 'required|integer|min:1',
            'tasks.*.done' => 'boolean',
        ]);

        $workLog = $this->workLogService->update($id, $data);

        return response()->json([
            'message' => __('Work log updated successfully.'),
            'data' => $workLog,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->workLogService->destroy($id);
        return response()->json(['message' => __('Work log deleted successfully.')]);
    }
}
