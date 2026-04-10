<?php

declare(strict_types=1);

namespace App\Services\EducationalObjective;

use App\DTOs\EducationalObjective\EducationalObjectiveDto;
use App\Models\EducationalObjective;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeEducationalObjectiveService
{
    /**
     * Get objectives for a specific user based on pivot.
     */
    public function getObjectivesForUser(User $user, array $filters = []): array
    {
        $query = $user->educationalObjectives()->withPivot(['status', 'progress_notes', 'completed_at']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->wherePivot('status', $filters['status']);
        }

        $objectives = $query->orderBy('target_date', 'asc')->get();

        return $objectives->map(function ($objective) {
            return EducationalObjectiveDto::fromModel($objective, $objective->pivot);
        })->toArray();
    }

    /**
     * Update progress notes for an objective.
     */
    public function updateProgress(User $user, int $objectiveId, string $notes): void
    {
        $objective = $user->educationalObjectives()->where('educational_objectives.id', $objectiveId)->firstOrFail();
        
        $currentStatus = $objective->pivot->status->value;
        $newStatus = $currentStatus === 'not_started' ? 'in_progress' : $currentStatus;

        $user->educationalObjectives()->updateExistingPivot($objectiveId, [
            'progress_notes' => $notes,
            'status' => $newStatus,
        ]);
    }

    /**
     * Mark an objective as completed.
     */
    public function markCompleted(User $user, int $objectiveId): void
    {
        $objective = $user->educationalObjectives()->where('educational_objectives.id', $objectiveId)->firstOrFail();

        $user->educationalObjectives()->updateExistingPivot($objectiveId, [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
