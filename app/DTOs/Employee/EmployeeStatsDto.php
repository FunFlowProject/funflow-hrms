<?php

declare(strict_types=1);

namespace App\DTOs\Employee;

class EmployeeStatsDto
{
    public function __construct(
        public array $employees,
        public array $pendingEmployees,
        public array $onboardingEmployees,
        public array $joinedEmployees,
        public array $terminatedEmployees,
    ) {}

    public static function fromQueryRow($stats): self
    {
        $stats = $stats ?? (object) [];

        return new self(
            employees: [
                'count' => (int) ($stats->total ?? 0),
                'lastUpdateTime' => formatDateTime($stats->last_update ?? null),
            ],
            pendingEmployees: [
                'count' => (int) ($stats->pending_count ?? 0),
                'lastUpdateTime' => formatDateTime($stats->pending_last_update ?? null),
            ],
            onboardingEmployees: [
                'count' => (int) ($stats->onboarding_count ?? 0),
                'lastUpdateTime' => formatDateTime($stats->onboarding_last_update ?? null),
            ],
            joinedEmployees: [
                'count' => (int) ($stats->joined_count ?? 0),
                'lastUpdateTime' => formatDateTime($stats->joined_last_update ?? null),
            ],
            terminatedEmployees: [
                'count' => (int) ($stats->terminated_count ?? 0),
                'lastUpdateTime' => formatDateTime($stats->terminated_last_update ?? null),
            ],
        );
    }
}
