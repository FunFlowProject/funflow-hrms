<?php

declare(strict_types=1);

namespace App\DTOs\Document;

final class DocumentUserStatusDto
{
    public function __construct(
        public readonly string $status,
        public readonly string $status_label,
        public readonly ?string $acknowledged_at,
    ) {}
}
