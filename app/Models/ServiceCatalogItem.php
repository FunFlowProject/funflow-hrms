<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ServiceCatalogItem Model
 *
 * Represents a service definition that can be requested by employees.
 *
 * @property int $id
 * @property string $name
 * @property string $category
 * @property string|null $description
 * @property bool $requires_justification
 * @property \App\Enums\ActiveStatus $active
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class ServiceCatalogItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'category',
        'description',
        'requires_justification',
        'active',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_justification' => 'boolean',
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

    // Get the user who created the service catalog item.
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Get the user who last updated the service catalog item.
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Get all service requests created from this catalog item.
    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'service_catalog_item_id');
    }
}
