<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentClassification;
use App\Enums\DocumentScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_type',
        'file_path',
        'classification',
        'scope_type',
        'scope_id',
        'requires_acknowledgment',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'classification' => DocumentClassification::class,
            'scope_type' => DocumentScope::class,
            'requires_acknowledgment' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'document_user')
            ->using(DocumentUser::class)
            ->withPivot(['status', 'acknowledged_at'])
            ->withTimestamps();
    }

    public function subCompany(): BelongsTo
    {
        return $this->belongsTo(SubCompany::class, 'scope_id');
    }

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class, 'scope_id');
    }
}
