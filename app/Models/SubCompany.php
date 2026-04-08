<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SubCompany Model
 *
 * Represents a sub-company entity in the organization.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \App\Enums\ActiveStatus $active
 */
class SubCompany extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
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

    // Get all squads that belong to the sub-company.
    public function squads(): HasMany
    {
        return $this->hasMany(Squad::class, 'sub_company_id');
    }

    // Get all employee assignments for this sub-company.
    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class, 'sub_company_id');
    }
}
