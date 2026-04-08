<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * EmployeeAssignment Model
 *
 * Represents a user's assignment to sub-company/squad/hierarchy.
 *
 * @property int $id
 * @property int $user_id
 * @property int $sub_company_id
 * @property int|null $squad_id
 * @property int $hierarchy_id
 * @property \App\Enums\ActiveStatus $active
 */
class EmployeeAssignment extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employee_assignments';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'sub_company_id',
        'squad_id',
        'hierarchy_id',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => ActiveStatus::class,
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', ActiveStatus::ACTIVE->value);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', ActiveStatus::INACTIVE->value);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Get the user for this assignment.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Get the sub-company for this assignment.
    public function subCompany(): BelongsTo
    {
        return $this->belongsTo(SubCompany::class, 'sub_company_id');
    }

    // Get the squad for this assignment.
    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class, 'squad_id');
    }

    // Get the hierarchy for this assignment.
    public function hierarchy(): BelongsTo
    {
        return $this->belongsTo(Hierarchy::class, 'hierarchy_id');
    }
}
