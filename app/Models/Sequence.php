<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sequence Model
 *
 * Tracks numeric sequences for various entities (e.g., User ID, Service Request ID).
 *
 * @property int $id
 * @property string $name Unique identifier for the sequence (e.g., 'user_id')
 * @property int $current_value The last issued value in the sequence
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Sequence extends Model
{
    protected $fillable = [
        'name',
        'current_value',
    ];
}
