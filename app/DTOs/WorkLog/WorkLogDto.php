<?php

namespace App\DTOs\WorkLog;

use App\Models\WorkLog;
use Illuminate\Support\Collection;

class WorkLogDto
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly string $user_name,
        public readonly array $tasks,
        public readonly int $total_duration_minutes,
        public readonly string $total_duration_formatted,
        public readonly string $created_at,
    ) {
    }

    public static function fromModel(WorkLog $workLog): self
    {
        return new self(
            id: $workLog->id,
            user_id: $workLog->user_id,
            user_name: $workLog->user?->full_name ?? 'Unknown',
            tasks: (array) $workLog->tasks,
            total_duration_minutes: $workLog->total_duration_minutes,
            total_duration_formatted: self::formatDuration($workLog->total_duration_minutes),
            created_at: $workLog->created_at->format('d M Y, h:i A'),
        );
    }

    public static function fromCollection(Collection $collection): array
    {
        return $collection->map(fn(WorkLog $model) => self::fromModel($model))->toArray();
    }

    private static function formatDuration(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$remainingMinutes}m";
        }

        return "{$remainingMinutes}m";
    }
}
