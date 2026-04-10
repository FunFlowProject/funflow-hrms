<?php

declare(strict_types=1);

namespace App\DTOs\Document;

final class DocumentStatsDto
{
    public function __construct(
        public readonly int $total,
        public readonly int $public,
        public readonly int $internal,
        public readonly int $confidential,
        public readonly ?string $last_update,
    ) {}

    public static function fromMetrics(array $metrics): self
    {
        return new self(
            total: $metrics['total'] ?? 0,
            public: $metrics['public'] ?? 0,
            internal: $metrics['internal'] ?? 0,
            confidential: $metrics['confidential'] ?? 0,
            last_update: $metrics['last_update'] ?? null,
        );
    }
}
