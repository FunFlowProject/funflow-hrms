<?php

declare(strict_types=1);

namespace App\DTOs\ServiceRequest;

class ServiceRequestStatsDto
{
    public function __construct(
        public array $totalRequests,
        public array $submittedRequests,
        public array $inProgressRequests,
        public array $completedRequests,
        public array $rejectedRequests,
    ) {}

    public static function fromMetrics(array $metrics): self
    {
        return new self(
            totalRequests: [
                'count' => (int) ($metrics['total'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            submittedRequests: [
                'count' => (int) ($metrics['submitted'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            inProgressRequests: [
                'count' => (int) ($metrics['in_progress'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            completedRequests: [
                'count' => (int) ($metrics['completed'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            rejectedRequests: [
                'count' => (int) ($metrics['rejected'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
        );
    }
}
