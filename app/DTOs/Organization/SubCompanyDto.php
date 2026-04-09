<?php

declare(strict_types=1);

namespace App\DTOs\Organization;

use App\Enums\ActiveStatus;
use App\Models\Squad;
use App\Models\SubCompany;

class SubCompanyDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public int $active,
        public string $active_label,
        public int $squads_count,
        public int $assignments_count,
        public array $squads,
        public string $created_at,
        public string $created_at_formatted,
        public string $updated_at,
        public string $updated_at_formatted,
    ) {}

    public static function fromModel(SubCompany $subCompany): self
    {
        $squadsCount = array_key_exists('squads_count', $subCompany->getAttributes())
            ? (int) $subCompany->squads_count
            : $subCompany->squads()->count();

        $assignmentsCount = array_key_exists('assignments_count', $subCompany->getAttributes())
            ? (int) $subCompany->assignments_count
            : $subCompany->assignments()->count();

        $squads = $subCompany->relationLoaded('squads')
            ? $subCompany->squads
            : collect();

        $active = ActiveStatus::safeFrom($subCompany->active) ?? ActiveStatus::INACTIVE;

        return new self(
            id: (int) $subCompany->id,
            name: $subCompany->name,
            description: $subCompany->description,
            active: $active->value,
            active_label: $active->label(),
            squads_count: $squadsCount,
            assignments_count: $assignmentsCount,
            squads: $squads
                ->map(function (Squad $squad): array {
                    $squadActive = ActiveStatus::safeFrom($squad->active) ?? ActiveStatus::INACTIVE;

                    return [
                        'id' => (int) $squad->id,
                        'name' => $squad->name,
                        'active' => $squadActive->value,
                        'active_label' => $squadActive->label(),
                    ];
                })
                ->values()
                ->all(),
            created_at: $subCompany->created_at->format('Y-m-d H:i:s'),
            created_at_formatted: $subCompany->created_at->format('d M Y, h:i A'),
            updated_at: $subCompany->updated_at->format('Y-m-d H:i:s'),
            updated_at_formatted: $subCompany->updated_at->format('d M Y, h:i A'),
        );
    }
}
