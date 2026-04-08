<?php

declare(strict_types=1);

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('service-requests.transition');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:3000'],
            'fulfillment_note' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
