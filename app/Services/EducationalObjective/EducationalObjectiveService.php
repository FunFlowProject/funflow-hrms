<?php

declare(strict_types=1);

namespace App\Services\EducationalObjective;

use App\DTOs\EducationalObjective\EducationalObjectiveDto;
use App\DTOs\EducationalObjective\EducationalObjectiveStatsDto;
use App\Models\EducationalObjective;
use App\Models\EmployeeAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class EducationalObjectiveService
{
    /**
     * Retrieve statistics for management portal.
     */
    public function stats(): EducationalObjectiveStatsDto
    {
        $user = Auth::user();
        $query = EducationalObjective::query();

        if (!$user->can('educational-objectives.manage-all')) {
            $query->where('created_by', $user->id);
        }

        $total = $query->count();
        
        $completedCount = 0;
        $inProgressCount = 0;
        $overdueCount = 0;

        // Subquery for counting statuses across users could be optimized, 
        // doing a simple calculation for MVP.
        $objectives = $query->with('employees')->get();
        foreach ($objectives as $objective) {
            foreach ($objective->employees as $emp) {
                if ($emp->pivot->status->value === 'completed') {
                    $completedCount++;
                } else {
                    if ($emp->pivot->status->value === 'in_progress') {
                        $inProgressCount++;
                    }
                    if ($objective->target_date && $objective->target_date->isPast() && $emp->pivot->status->value !== 'completed') {
                        $overdueCount++;
                    }
                }
            }
        }

        return new EducationalObjectiveStatsDto(
            total: $total,
            completed: $completedCount,
            overdue: $overdueCount,
            in_progress: $inProgressCount,
            last_update: now()->format('Y-m-d H:i:s')
        );
    }

    /**
     * Retrieve data for DataTables.
     */
    public function datatable(): JsonResponse
    {
        $user = Auth::user();
        $query = EducationalObjective::query()->withCount(['employees as users_count', 'employees as completed_users_count' => function ($query) {
            $query->where('educational_objective_user.status', 'completed');
        }]);

        if (!$user->can('educational-objectives.manage-all')) {
            $query->where('created_by', $user->id);
        }

        return DataTables::of($query)
            ->filter(function ($query) {
                if (request()->filled('search_name')) {
                    $query->where('name', 'like', '%' . request('search_name') . '%');
                }
                if (request()->filled('search_priority')) {
                    $query->where('priority', request('search_priority'));
                }
                if (request()->filled('search_scope')) {
                    $query->where('scope_type', request('search_scope'));
                }
            })
            ->editColumn('name', function ($objective) {
                return $objective->name;
            })
            ->editColumn('priority', function ($objective) {
                return $objective->priority->label();
            })
            ->addColumn('scope_label', function ($objective) {
                return $objective->scope_type->label();
            })
            ->editColumn('target_date', function ($objective) {
                return $objective->target_date ? $objective->target_date->format('M d, Y') : '-';
            })
            ->addColumn('progress', function ($objective) {
                $total = $objective->users_count;
                $completed = $objective->completed_users_count;
                if ($total == 0) return '0%';
                $pct = round(($completed / $total) * 100);
                return "{$pct}% ({$completed}/{$total})";
            })
            ->addColumn('actions', function ($objective) {
                return $this->renderActionButtons($objective);
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Store a new educational objective.
     */
    public function create(array $data, ?\Illuminate\Http\UploadedFile $file): EducationalObjectiveDto
    {
        return DB::transaction(function () use ($data, $file) {
            $objectiveData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'mandatory' => !empty($data['mandatory']),
                'target_date' => $data['target_date'] ?? null,
                'priority' => $data['priority'],
                'scope_type' => $data['scope_type'],
                'scope_id' => $data['scope_id'] ?? null,
                'created_by' => Auth::id(),
            ];

            if ($file) {
                $path = $file->store('educational_objectives', 'public');
                $objectiveData['attachment'] = $path;
            } elseif (!empty($data['attachment_url'])) {
                $objectiveData['attachment'] = $data['attachment_url'];
            }

            $objective = EducationalObjective::create($objectiveData);

            $this->assignToUsers($objective);

            return EducationalObjectiveDto::fromModel($objective);
        });
    }

    /**
     * Assign the objective to the applicable users based on the scope.
     */
    protected function assignToUsers(EducationalObjective $objective): void
    {
        $userIds = [];

        if ($objective->scope_type->value === 'company') {
            $userIds = User::pluck('id')->toArray();
        } elseif ($objective->scope_type->value === 'sub_company') {
            $userIds = EmployeeAssignment::where('sub_company_id', $objective->scope_id)->pluck('user_id')->toArray();
        } elseif ($objective->scope_type->value === 'squad') {
            $userIds = EmployeeAssignment::where('squad_id', $objective->scope_id)->pluck('user_id')->toArray();
        } elseif ($objective->scope_type->value === 'individual') {
            $userIds = [$objective->scope_id];
        }

        // Apply manager restrictions if not HR
        // The implementation plan mandates that a manager can only assign to direct reports (someone in the same squad with a lower rank i.e. numerically higher hierarchy level)
        // For sub-company or company, a standard manager isn't allowed anyway via validation, but we can double check here.
        if (!Auth::user()->can('educational-objectives.manage-all')) {
            $manager = Auth::user();
            $managerAssignments = $manager->assignments()->with('hierarchy')->get();
            
            $allowedUserIds = [];
            
            foreach ($managerAssignments as $assignment) {
                if (!$assignment->squad_id || !$assignment->hierarchy) continue;
                
                $managerLevel = $assignment->hierarchy->level;
                
                $subordinates = EmployeeAssignment::where('squad_id', $assignment->squad_id)
                    ->whereHas('hierarchy', function($q) use ($managerLevel) {
                        $q->where('level', '>', $managerLevel);
                    })
                    ->pluck('user_id')->toArray();
                    
                $allowedUserIds = array_merge($allowedUserIds, $subordinates);
            }
            
            $userIds = array_intersect($userIds, $allowedUserIds);
        }

        if (!empty($userIds)) {
            $pivotData = array_fill_keys(array_unique($userIds), ['status' => 'not_started']);
            $objective->employees()->syncWithoutDetaching($pivotData);
        }
    }

    /**
     * Delete an objective.
     */
    public function destroy(int $id): bool
    {
        $objective = EducationalObjective::findOrFail($id);
        
        // Ensure user can delete
        if (!Auth::user()->can('educational-objectives.manage-all') && $objective->created_by !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($objective->attachment && !filter_var($objective->attachment, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($objective->attachment);
        }

        return $objective->delete();
    }

    /**
     * Generate HTML for actions cell.
     */
    protected function renderActionButtons(EducationalObjective $objective): string
    {
        $actions = [
            [
                'label' => 'Delete Objective',
                'class' => 'text-danger deleteObjectiveBtn',
                'icon' => 'bx bx-trash',
                'modal' => false,
                'data' => [
                    'id' => $objective->id,
                    'name' => $objective->name,
                ]
            ],
        ];

        return view('components.ui.table-actions', [
            'mode' => 'dropdown',
            'actions' => $actions,
            'id' => $objective->id,
            'type' => 'EducationalObjective',
            'deleteName' => $objective->name,
            'deleteClass' => 'deleteObjectiveBtn',
        ])->render();
    }
}
