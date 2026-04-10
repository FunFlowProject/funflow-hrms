<?php

declare(strict_types=1);

namespace App\Http\Requests\EducationalObjective;

use Illuminate\Foundation\Http\FormRequest;

class UpdateObjectiveProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user evaluating their own objectives
    }

    public function rules(): array
    {
        return [
            'progress_notes' => ['required', 'string', 'max:1000'],
        ];
    }
}
