<?php

declare(strict_types=1);

namespace App\DTOs\ServiceRequest;

use App\Enums\ActiveStatus;
use App\Models\ServiceCatalogItem;

class ServiceCatalogDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $category,
        public ?string $description,
        public bool $requires_justification,
        public string $requires_justification_label,
        public int $active,
        public string $active_label,
        public ?int $created_by,
        public ?string $created_by_name,
        public ?int $updated_by,
        public ?string $updated_by_name,
        public string $created_at,
        public string $created_at_formatted,
        public string $updated_at,
        public string $updated_at_formatted,
    ) {}

    public static function fromModel(ServiceCatalogItem $serviceCatalogItem): self
    {
        $active = ActiveStatus::safeFrom($serviceCatalogItem->active) ?? ActiveStatus::INACTIVE;

        $createdByName = $serviceCatalogItem->relationLoaded('creator') && $serviceCatalogItem->creator
            ? $serviceCatalogItem->creator->full_name
            : $serviceCatalogItem->creator()->value('full_name');

        $updatedByName = $serviceCatalogItem->relationLoaded('updater') && $serviceCatalogItem->updater
            ? $serviceCatalogItem->updater->full_name
            : $serviceCatalogItem->updater()->value('full_name');

        $requiresJustification = (bool) $serviceCatalogItem->requires_justification;

        return new self(
            id: $serviceCatalogItem->id,
            name: $serviceCatalogItem->name,
            category: $serviceCatalogItem->category,
            description: $serviceCatalogItem->description,
            requires_justification: $requiresJustification,
            requires_justification_label: $requiresJustification ? __('Yes') : __('No'),
            active: $active->value,
            active_label: $active->label(),
            created_by: $serviceCatalogItem->created_by,
            created_by_name: $createdByName,
            updated_by: $serviceCatalogItem->updated_by,
            updated_by_name: $updatedByName,
            created_at: $serviceCatalogItem->created_at->format('Y-m-d H:i:s'),
            created_at_formatted: $serviceCatalogItem->created_at->format('d M Y, h:i A'),
            updated_at: $serviceCatalogItem->updated_at->format('Y-m-d H:i:s'),
            updated_at_formatted: $serviceCatalogItem->updated_at->format('d M Y, h:i A'),
        );
    }
}
