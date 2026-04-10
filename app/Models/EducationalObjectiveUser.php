<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EducationalObjectiveStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * EducationalObjectiveUser Pivot Model
 *
 * @property int $id
 * @property int $educational_objective_id
 * @property int $user_id
 * @property EducationalObjectiveStatus $status
 * @property string|null $progress_notes
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EducationalObjectiveUser extends Pivot
{
    protected $table = 'educational_objective_user';

    public $incrementing = true;

    protected $fillable = [
        'educational_objective_id',
        'user_id',
        'status',
        'progress_notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EducationalObjectiveStatus::class,
            'completed_at' => 'datetime',
        ];
    }
}
