<?php

namespace App\Services\WorkLog;

use App\DTOs\WorkLog\WorkLogDto;
use App\Models\WorkLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class WorkLogService
{
    /**
     * DataTables endpoint for work logs.
     */
    public function datatable(): JsonResponse
    {
        $actor = Auth::user();
        $query = WorkLog::query()->with('user:id,full_name');

        // Scope logs if not admin
        if (!$actor->can('work-logs.view-all')) {
            $query->where('user_id', $actor->id);
        }

        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee', fn (WorkLog $log) => $log->user?->full_name ?? '—')
            ->addColumn('total_duration', function (WorkLog $log) {
                $hours = floor($log->total_duration_minutes / 60);
                $minutes = $log->total_duration_minutes % 60;
                return "{$hours}h {$minutes}m";
            })
            ->addColumn('tasks_count', fn (WorkLog $log) => count($log->tasks))
            ->addColumn('actions', fn (WorkLog $log) => $this->renderActions($log))
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Store a new work log.
     */
    public function store(array $data): WorkLogDto
    {
        $actor = Auth::user();
        $tasks = (array) ($data['tasks'] ?? []);
        $totalDuration = collect($tasks)->sum('duration_minutes');

        $workLog = WorkLog::create([
            'user_id' => $actor->id,
            'tasks' => $tasks,
            'total_duration_minutes' => (int) $totalDuration,
        ]);

        return WorkLogDto::fromModel($workLog->load('user'));
    }

    /**
     * Update an existing work log.
     */
    public function update(int $id, array $data): WorkLogDto
    {
        $workLog = WorkLog::findOrFail($id);
        $tasks = (array) ($data['tasks'] ?? []);
        $totalDuration = collect($tasks)->sum('duration_minutes');

        $workLog->update([
            'tasks' => $tasks,
            'total_duration_minutes' => (int) $totalDuration,
        ]);

        return WorkLogDto::fromModel($workLog->load('user'));
    }

    /**
     * Retrieve a specific work log.
     */
    public function show(int $id): WorkLogDto
    {
        $workLog = WorkLog::with('user')->findOrFail($id);
        return WorkLogDto::fromModel($workLog);
    }

    /**
     * Delete a work log.
     */
    public function destroy(int $id): bool
    {
        $workLog = WorkLog::findOrFail($id);
        return $workLog->delete();
    }

    private function renderActions(WorkLog $log): string
    {
        $actor = Auth::user();
        $actions = ['view'];

        // Only allow edit/delete if own log AND within 24h (optional logic, but good practice)
        // For now, let's keep it simple as per user request
        if ($log->user_id === $actor->id || $actor->can('work-logs.manage')) {
            $actions[] = 'edit';
            $actions[] = 'delete';
        }

        return view('components.ui.table-actions', [
            'mode' => 'dropdown',
            'actions' => $actions,
            'id' => $log->id,
            'type' => 'WorkLog',
        ])->render();
    }
}
