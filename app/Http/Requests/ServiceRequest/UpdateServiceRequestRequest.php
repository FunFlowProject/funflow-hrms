<?php

declare(strict_types=1);

namespace App\Http\Requests\ServiceRequest;

use App\Enums\ActiveStatus;
use App\Models\ServiceCatalogItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user?->can('service-requests.create') || (bool) $user?->can('service-requests.manage');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_catalog_item_id' => ['required', 'integer', 'exists:service_catalog_items,id'],
            'justification' => [
                'nullable',
                'string',
                'max:3000',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $itemId = (int) $this->input('service_catalog_item_id');
                    $serviceItem = ServiceCatalogItem::query()->find($itemId);

                    if (!$serviceItem) {
                        return;
                    }

                    $active = ActiveStatus::safeFrom($serviceItem->active) ?? ActiveStatus::INACTIVE;

                    if ($active !== ActiveStatus::ACTIVE) {
                        $fail(__('The selected service is currently unavailable.'));

                        return;
                    }

                    if ($serviceItem->requires_justification && blank($value)) {
                        $fail(__('A justification is required for the selected service.'));
                    }
                },
            ],
        ];
    }
}
