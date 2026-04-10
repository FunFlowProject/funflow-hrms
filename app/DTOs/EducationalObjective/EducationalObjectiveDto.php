<?php

declare(strict_types=1);

namespace App\DTOs\EducationalObjective;

use App\Models\EducationalObjective;

class EducationalObjectiveDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public bool $mandatory,
        public ?string $target_date,
        public string $priority,
        public string $priority_label,
        public ?string $attachment,
        public string $scope_type,
        public string $scope_type_label,
        public ?int $scope_id,
        public ?string $scope_name,
        public string $created_at,
        public string $updated_at,
        
        // For employee view
        public ?EducationalObjectiveUserStatusDto $employee_status = null,
        
        // For admin datatable view
        public ?int $assignments_count = null,
        public ?int $completed_count = null,
    ) {}

    public static function fromModel(EducationalObjective $objective, ?\App\Models\EducationalObjectiveUser $userPivot = null): self
    {
        $scopeName = null;
        if ($objective->scope_type->value === 'sub_company' && $objective->scope_id) {
            $scopeName = \App\Models\SubCompany::find($objective->scope_id)?->name;
        } elseif ($objective->scope_type->value === 'squad' && $objective->scope_id) {
            $scopeName = \App\Models\Squad::find($objective->scope_id)?->name;
        } elseif ($objective->scope_type->value === 'individual' && $objective->scope_id) {
            $scopeName = \App\Models\User::find($objective->scope_id)?->full_name;
        }

        return new self(
            id: (int) $objective->id,
            name: $objective->name,
            description: $objective->description,
            mandatory: $objective->mandatory,
            target_date: $objective->target_date?->format('Y-m-d'),
            priority: $objective->priority->value,
            priority_label: $objective->priority->label(),
            attachment: $objective->attachment,
            scope_type: $objective->scope_type->value,
            scope_type_label: $objective->scope_type->label(),
            scope_id: $objective->scope_id ? (int) $objective->scope_id : null,
            scope_name: $scopeName,
            created_at: $objective->created_at->format('M d, Y'),
            updated_at: $objective->updated_at->format('M d, Y H:i'),
            employee_status: $userPivot ? EducationalObjectiveUserStatusDto::fromModel($userPivot) : null,
            assignments_count: $objective->users_count ?? null,
            completed_count: $objective->completed_users_count ?? null,
        );
    }
}
