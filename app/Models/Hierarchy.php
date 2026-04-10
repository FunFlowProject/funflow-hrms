<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Hierarchy Model
 *
 * Represents a hierarchy level/role in the organization.
 *
 * @property int $id
 * @property int $level
 * @property string $title
 * @property string $scope
 * @property string $type
 * @property \App\Enums\ActiveStatus $active
 */
class Hierarchy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'level',
        'title',
        'scope',
        'type',
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

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('active', ActiveStatus::ACTIVE->value);
    }

    #[Scope]
    protected function inactive(Builder $query): void
    {
        $query->where('active', ActiveStatus::INACTIVE->value);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Get all employee assignments mapped to this hierarchy.
    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class, 'hierarchy_id');
    }
}
