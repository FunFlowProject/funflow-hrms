<?php

declare(strict_types=1);

namespace App\DTOs\Document;

use App\Models\Document;

final class DocumentDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $file_type,
        public readonly string $file_path,
        public readonly string $classification,
        public readonly string $classification_label,
        public readonly string $scope_type,
        public readonly string $scope_type_label,
        public readonly ?int $scope_id,
        public readonly bool $requires_acknowledgment,
        public readonly string $created_at,
        public readonly ?string $scope_name = null,
        public readonly ?DocumentUserStatusDto $employee_status = null,
    ) {}

    public static function fromModel(Document $document, ?DocumentUserStatusDto $employeeStatus = null): self
    {
        $scopeName = null;
        if ($document->scope_id) {
            if ($document->scope_type->value === 'sub_company' && $document->relationLoaded('subCompany')) {
                $scopeName = $document->subCompany?->name;
            } elseif ($document->scope_type->value === 'squad' && $document->relationLoaded('squad')) {
                $scopeName = $document->squad?->name;
            }
        }

        return new self(
            id: $document->id,
            name: $document->name,
            file_type: $document->file_type,
            file_path: $document->file_path,
            classification: $document->classification->value,
            classification_label: $document->classification->label(),
            scope_type: $document->scope_type->value,
            scope_type_label: $document->scope_type->label(),
            scope_id: $document->scope_id,
            requires_acknowledgment: $document->requires_acknowledgment,
            created_at: $document->created_at->format('d M Y'),
            scope_name: $scopeName,
            employee_status: $employeeStatus,
        );
    }
}
