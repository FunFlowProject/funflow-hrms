<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Casts the tasks JSON column to/from a plain array.
 * Each task: ['name' => string, 'duration_minutes' => int, 'done' => bool]
 */
class TasksCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return json_decode($value ?? '[]', true) ?? [];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode(array_values((array) $value));
    }
}
