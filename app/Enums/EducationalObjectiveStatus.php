<?php

declare(strict_types=1);

namespace App\Enums;

enum EducationalObjectiveStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => __('Not Started'),
            self::InProgress => __('In Progress'),
            self::Completed => __('Completed'),
        };
    }
}
