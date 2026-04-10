<?php

declare(strict_types=1);

namespace App\DTOs\EducationalObjective;

use App\Models\EducationalObjectiveUser;

class EducationalObjectiveUserStatusDto
{
    public function __construct(
        public string $status,
        public string $status_label,
        public ?string $progress_notes,
        public ?string $completed_at,
    ) {}

    public static function fromModel(EducationalObjectiveUser $pivot): self
    {
        return new self(
            status: $pivot->status->value,
            status_label: $pivot->status->label(),
            progress_notes: $pivot->progress_notes,
            completed_at: $pivot->completed_at?->format('Y-m-d H:i:s'),
        );
    }
}
