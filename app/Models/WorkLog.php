<?php

namespace App\Models;

use App\Casts\TasksCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tasks',
        'total_duration_minutes',
    ];

    protected $casts = [
        'tasks' => TasksCast::class,
        'total_duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
