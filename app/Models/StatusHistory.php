<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * StatusHistory Model
 *
 * Stores status transitions for any statusable entity.
 *
 * @property int $id
 * @property string $statusable_type
 * @property int $statusable_id
 * @property int|null $actor_id
 * @property string|null $from_status
 * @property string $to_status
 * @property string|null $action
 * @property string|null $note
 * @property array<string, mixed>|null $details
 * @property \Carbon\Carbon $recorded_at
 */
class StatusHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'statusable_type',
        'statusable_id',
        'actor_id',
        'from_status',
        'to_status',
        'action',
        'note',
        'details',
        'recorded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Get the statusable entity whose status changed.
    public function statusable(): MorphTo
    {
        return $this->morphTo();
    }

    // Get the user who performed the status change.
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}