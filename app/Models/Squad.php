<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Squad Model
 *
 * Represents a squad within a sub-company.
 *
 * @property int $id
 * @property int $sub_company_id
 * @property string $name
 * @property \App\Enums\ActiveStatus $active
 */
class Squad extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sub_company_id',
        'name',
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

    // Get the sub-company that this squad belongs to.
    public function subCompany(): BelongsTo
    {
        return $this->belongsTo(SubCompany::class, 'sub_company_id');
    }

    // Get all employee assignments in this squad.
    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class, 'squad_id');
    }
}
