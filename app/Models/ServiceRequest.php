<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * ServiceRequest Model
 *
 * Represents a service request submitted by an employee.
 *
 * @property int $id
 * @property int|null $service_catalog_item_id
 * @property string $service_name_snapshot
 * @property string $service_category_snapshot
 * @property bool $service_requires_justification_snapshot
 * @property int $requester_id
 * @property int|null $handled_by
 * @property \App\Enums\ServiceRequestStatus $status
 * @property string|null $justification
 * @property string|null $fulfillment_note
 * @property string|null $rejection_reason
 * @property \Carbon\Carbon|null $acted_at
 */
class ServiceRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'service_catalog_item_id',
        'service_name_snapshot',
        'service_category_snapshot',
        'service_requires_justification_snapshot',
        'requester_id',
        'handled_by',
        'status',
        'justification',
        'fulfillment_note',
        'rejection_reason',
        'acted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_requires_justification_snapshot' => 'boolean',
            'status' => ServiceRequestStatus::class,
            'acted_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function mine(Builder $query, int $userId): void
    {
        $query->where('requester_id', $userId);
    }

    #[Scope]
    protected function manageableBy(Builder $query, ?User $user): void
    {
        if (!$user) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($user->can('service-requests.manage')) {
            return;
        }

        $query->where('requester_id', $user->id);
    }

    #[Scope]
    protected function withStatus(Builder $query, ServiceRequestStatus|string|null $status): void
    {
        $enum = ServiceRequestStatus::safeFrom($status);

        if ($enum) {
            $query->where('status', $enum->value);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Get the selected service catalog item.
    public function serviceCatalogItem(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalogItem::class, 'service_catalog_item_id');
    }

    // Get the request creator.
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    // Get the user currently handling this request.
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    // Get status history entries for this statusable entity.
    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable')
            ->orderByDesc('recorded_at');
    }
}
