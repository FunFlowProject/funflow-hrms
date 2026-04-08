<?php

declare(strict_types=1);

namespace App\DTOs\Organization;

class SubCompanyStatsDto
{
    public function __construct(
        public array $subCompanies,
        public array $withSquads,
        public array $withoutSquads,
        public array $withAssignments,
    ) {}

    public static function fromMetrics(array $metrics): self
    {
        return new self(
            subCompanies: [
                'count' => (int) ($metrics['total'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            withSquads: [
                'count' => (int) ($metrics['with_squads'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            withoutSquads: [
                'count' => (int) ($metrics['without_squads'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            withAssignments: [
                'count' => (int) ($metrics['with_assignments'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
        );
    }
}
