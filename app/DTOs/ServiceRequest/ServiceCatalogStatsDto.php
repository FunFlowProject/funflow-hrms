<?php

declare(strict_types=1);

namespace App\DTOs\ServiceRequest;

class ServiceCatalogStatsDto
{
    public function __construct(
        public array $serviceCatalog,
        public array $activeServices,
        public array $requiresJustification,
        public array $categories,
    ) {}

    public static function fromMetrics(array $metrics): self
    {
        return new self(
            serviceCatalog: [
                'count' => (int) ($metrics['total'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            activeServices: [
                'count' => (int) ($metrics['active'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            requiresJustification: [
                'count' => (int) ($metrics['requires_justification'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
            categories: [
                'count' => (int) ($metrics['categories'] ?? 0),
                'lastUpdateTime' => $metrics['last_update'] ?? null,
            ],
        );
    }
}
