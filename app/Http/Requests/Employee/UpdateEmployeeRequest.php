<?php

namespace App\Http\Requests\Employee;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('employees.update');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'full_name' => ['required_without:name', 'string', 'min:3', 'max:100', 'regex:/^\S+\s+\S+/'],
            'name' => ['required_without:full_name', 'string', 'min:3', 'max:100', 'regex:/^\S+\s+\S+/'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employeeId)],
            'phone' => ['required_without:phone_number', 'string', 'max:30', Rule::unique('users', 'phone_number')->ignore($employeeId)],
            'date_of_birth' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->toDateString()],
            'hire_date' => ['required', 'date', 'before_or_equal:today'],
            'contract_type' => ['required', 'in:full-time,intern,ambassador'],
            'system_role' => ['required', 'in:admin,hr,employee'],

            'assignments' => ['nullable', 'array', 'required_if:system_role,employee'],
            'assignments.*.sub_company_id' => ['required_if:system_role,employee', 'integer', 'exists:sub_companies,id'],
            'assignments.*.squad_id' => ['nullable', 'integer', 'exists:squads,id'],
            'assignments.*.hierarchy_id' => ['required_if:system_role,employee', 'integer', 'exists:hierarchies,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.regex' => 'The full name must consist of at least two parts (e.g., First Name and Last Name).',
            'name.regex' => 'The name must consist of at least two parts (e.g., First Name and Last Name).',
            'date_of_birth.before_or_equal' => 'Employee must be at least 18 years old.',
        ];
    }
}
