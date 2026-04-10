<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EducationalObjectivePriority;
use App\Enums\EducationalObjectiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * EducationalObjective Model
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $mandatory
 * @property \Carbon\Carbon|null $target_date
 * @property EducationalObjectivePriority $priority
 * @property string|null $attachment
 * @property EducationalObjectiveScope $scope_type
 * @property int|null $scope_id
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EducationalObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'mandatory',
        'target_date',
        'priority',
        'attachment',
        'scope_type',
        'scope_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'mandatory' => 'boolean',
            'target_date' => 'date',
            'priority' => EducationalObjectivePriority::class,
            'scope_type' => EducationalObjectiveScope::class,
            'scope_id' => 'integer',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'educational_objective_user')
            ->using(EducationalObjectiveUser::class)
            ->withPivot(['status', 'progress_notes', 'completed_at'])
            ->withTimestamps();
    }
}
