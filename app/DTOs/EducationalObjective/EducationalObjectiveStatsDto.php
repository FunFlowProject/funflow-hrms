<?php

declare(strict_types=1);

namespace App\DTOs\EducationalObjective;

class EducationalObjectiveStatsDto
{
    public function __construct(
        public int $total,
        public int $completed,
        public int $overdue,
        public int $in_progress,
        public ?string $last_update = null,
    ) {}
}
