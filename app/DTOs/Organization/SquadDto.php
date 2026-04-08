<?php

declare(strict_types=1);

namespace App\DTOs\Organization;

use App\Enums\ActiveStatus;
use App\Models\Squad;

class SquadDto
{
    public function __construct(
        public int $id,
        public int $sub_company_id,
        public string $sub_company_name,
        public string $name,
        public int $active,
        public string $active_label,
        public int $assignments_count,
        public string $created_at,
        public string $created_at_formatted,
        public string $updated_at,
        public string $updated_at_formatted,
    ) {}

    public static function fromModel(Squad $squad): self
    {
        $assignmentsCount = array_key_exists('assignments_count', $squad->getAttributes())
            ? (int) $squad->assignments_count
            : $squad->assignments()->count();

        $subCompanyName = $squad->relationLoaded('subCompany') && $squad->subCompany
            ? $squad->subCompany->name
            : (string) $squad->subCompany()->value('name');

        $active = ActiveStatus::safeFrom($squad->active) ?? ActiveStatus::INACTIVE;

        return new self(
            id: $squad->id,
            sub_company_id: $squad->sub_company_id,
            sub_company_name: $subCompanyName,
            name: $squad->name,
            active: $active->value,
            active_label: $active->label(),
            assignments_count: $assignmentsCount,
            created_at: $squad->created_at->format('Y-m-d H:i:s'),
            created_at_formatted: $squad->created_at->format('d M Y, h:i A'),
            updated_at: $squad->updated_at->format('Y-m-d H:i:s'),
            updated_at_formatted: $squad->updated_at->format('d M Y, h:i A'),
        );
    }
}
