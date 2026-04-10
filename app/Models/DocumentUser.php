<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentEmployeeStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DocumentUser extends Pivot
{
    protected $table = 'document_user';

    protected function casts(): array
    {
        return [
            'status' => DocumentEmployeeStatus::class,
            'acknowledged_at' => 'datetime',
        ];
    }
}
