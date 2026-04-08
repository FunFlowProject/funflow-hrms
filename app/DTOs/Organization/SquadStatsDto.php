<?php

declare(strict_types=1);

namespace App\DTOs\Organization;

class SquadStatsDto
{
    public function __construct(
        public array $squads,
        public array $withAssignments,
        public array $withoutAssignments,
        public array $coveredSubCompanies,
    ) {}

    public static function fromMetrics(array $metrics): self
    {
        return new self(
            squads: [
                'count' => (int) ($metrics['total'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            withAssignments: [
                'count' => (int) ($metrics['with_assignments'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            withoutAssignments: [
                'count' => (int) ($metrics['without_assignments'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            coveredSubCompanies: [
                'count' => (int) ($metrics['covered_sub_companies'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
        );
    }
}
