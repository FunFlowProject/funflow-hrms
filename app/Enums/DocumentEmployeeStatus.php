<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentEmployeeStatus: string
{
    case New = 'new';
    case Viewed = 'viewed';
    case Acknowledged = 'acknowledged';

    public function label(): string
    {
        return match ($this) {
            self::New => __('New'),
            self::Viewed => __('Viewed'),
            self::Acknowledged => __('Acknowledged'),
        };
    }
}
