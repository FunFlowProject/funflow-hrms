<?php

declare(strict_types=1);

namespace App\DTOs\ServiceRequest;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;

class ServiceRequestDto
{
    public function __construct(
        public int $id,
        public ?int $service_catalog_item_id,
        public string $service_name,
        public string $service_category,
        public bool $service_requires_justification,
        public int $requester_id,
        public string $requester_name,
        public string $requester_email,
        public ?int $handled_by,
        public ?string $handled_by_name,
        public string $status,
        public string $status_label,
        public ?string $justification,
        public ?string $fulfillment_note,
        public ?string $rejection_reason,
        public ?string $acted_at,
        public ?string $acted_at_formatted,
        public string $created_at,
        public string $created_at_formatted,
        public string $updated_at,
        public string $updated_at_formatted,
    ) {}

    public static function fromModel(ServiceRequest $serviceRequest): self
    {
        $status = ServiceRequestStatus::safeFrom($serviceRequest->status) ?? ServiceRequestStatus::Submitted;

        $serviceName = $serviceRequest->service_name_snapshot;
        $serviceCategory = $serviceRequest->service_category_snapshot;
        $serviceRequiresJustification = (bool) $serviceRequest->service_requires_justification_snapshot;

        if ($serviceRequest->relationLoaded('serviceCatalogItem') && $serviceRequest->serviceCatalogItem) {
            $serviceName = $serviceRequest->serviceCatalogItem->name;
            $serviceCategory = $serviceRequest->serviceCatalogItem->category;
            $serviceRequiresJustification = (bool) $serviceRequest->serviceCatalogItem->requires_justification;
        }

        $requesterName = $serviceRequest->relationLoaded('requester') && $serviceRequest->requester
            ? $serviceRequest->requester->full_name
            : (string) $serviceRequest->requester()->value('full_name');

        $requesterEmail = $serviceRequest->relationLoaded('requester') && $serviceRequest->requester
            ? $serviceRequest->requester->email
            : (string) $serviceRequest->requester()->value('email');

        $handledByName = $serviceRequest->relationLoaded('handler') && $serviceRequest->handler
            ? $serviceRequest->handler->full_name
            : $serviceRequest->handler()->value('full_name');

        return new self(
            id: (int) $serviceRequest->id,
            service_catalog_item_id: $serviceRequest->service_catalog_item_id !== null ? (int) $serviceRequest->service_catalog_item_id : null,
            service_name: $serviceName,
            service_category: $serviceCategory,
            service_requires_justification: $serviceRequiresJustification,
            requester_id: (int) $serviceRequest->requester_id,
            requester_name: $requesterName,
            requester_email: $requesterEmail,
            handled_by: $serviceRequest->handled_by !== null ? (int) $serviceRequest->handled_by : null,
            handled_by_name: $handledByName,
            status: $status->value,
            status_label: $status->label(),
            justification: $serviceRequest->justification,
            fulfillment_note: $serviceRequest->fulfillment_note,
            rejection_reason: $serviceRequest->rejection_reason,
            acted_at: $serviceRequest->acted_at?->format('Y-m-d H:i:s'),
            acted_at_formatted: $serviceRequest->acted_at?->format('d M Y, h:i A'),
            created_at: $serviceRequest->created_at->format('Y-m-d H:i:s'),
            created_at_formatted: $serviceRequest->created_at->format('d M Y, h:i A'),
            updated_at: $serviceRequest->updated_at->format('Y-m-d H:i:s'),
            updated_at_formatted: $serviceRequest->updated_at->format('d M Y, h:i A'),
        );
    }
}
