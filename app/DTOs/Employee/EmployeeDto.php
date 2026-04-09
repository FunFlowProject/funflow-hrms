<?php

declare(strict_types=1);

namespace App\DTOs\Employee;

use App\Models\EmployeeAssignment;
use App\Models\User;

class EmployeeDto
{
    public function __construct(
        public int $id,
        public string $full_name,
        public string $email,
        public ?string $username,
        public ?string $phone_number,
        public ?string $date_of_birth,
        public ?string $date_of_birth_formatted,
        public ?string $hire_date,
        public ?string $hire_date_formatted,
        public string $contract_type,
        public string $contract_type_label,
        public string $system_role,
        public string $system_role_label,
        public string $status,
        public string $status_label,
        public array $assignments,
        public ?string $email_verified_at,
        public ?string $email_verified_at_formatted,
        public string $created_at,
        public string $created_at_formatted,
        public string $updated_at,
        public string $updated_at_formatted,
    ) {}

    public static function fromModel(User $user): self
    {
        $assignments = $user->relationLoaded('assignments')
            ? $user->assignments
            : $user->assignments()->with(['subCompany:id,name', 'squad:id,name', 'hierarchy:id,title,level,type'])->get();

        return new self(
            id: (int) $user->id,
            full_name: $user->full_name,
            email: $user->email,
            username: $user->username,
            phone_number: $user->phone_number,
            date_of_birth: $user->date_of_birth?->format('Y-m-d'),
            date_of_birth_formatted: $user->date_of_birth?->format('d M Y'),
            hire_date: $user->hire_date?->format('Y-m-d'),
            hire_date_formatted: $user->hire_date?->format('d M Y'),
            contract_type: $user->contract_type->value,
            contract_type_label: $user->contract_type->label(),
            system_role: $user->system_role->value,
            system_role_label: $user->system_role->label(),
            status: $user->status->value,
            status_label: $user->status->label(),
            assignments: $assignments
                ->map(fn (EmployeeAssignment $assignment): array => [
                    'id' => (int) $assignment->id,
                    'sub_company_id' => (int) $assignment->sub_company_id,
                    'squad_id' => (int) $assignment->squad_id,
                    'hierarchy_id' => (int) $assignment->hierarchy_id,
                    'sub_company' => $assignment->relationLoaded('subCompany') && $assignment->subCompany
                        ? [
                            'id' => (int) $assignment->subCompany->id,
                            'name' => $assignment->subCompany->name,
                        ]
                        : null,
                    'squad' => $assignment->relationLoaded('squad') && $assignment->squad
                        ? [
                            'id' => (int) $assignment->squad->id,
                            'name' => $assignment->squad->name,
                        ]
                        : null,
                    'hierarchy' => $assignment->relationLoaded('hierarchy') && $assignment->hierarchy
                        ? [
                            'id' => (int) $assignment->hierarchy->id,
                            'title' => $assignment->hierarchy->title,
                            'level' => $assignment->hierarchy->level,
                            'type' => $assignment->hierarchy->type,
                        ]
                        : null,
                ])
                ->values()
                ->all(),
            email_verified_at: $user->email_verified_at?->format('Y-m-d H:i:s'),
            email_verified_at_formatted: $user->email_verified_at?->format('d M Y, h:i A'),
            created_at: $user->created_at->format('Y-m-d H:i:s'),
            created_at_formatted: $user->created_at->format('d M Y, h:i A'),
            updated_at: $user->updated_at->format('Y-m-d H:i:s'),
            updated_at_formatted: $user->updated_at->format('d M Y, h:i A'),
        );
    }
}
