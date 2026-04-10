<?php

declare(strict_types=1);

namespace App\Enums;

enum EducationalObjectivePriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => __('Low'),
            self::Medium => __('Medium'),
            self::High => __('High'),
        };
    }

    public static function options(): array
    {
        return [
            self::Low->value => self::Low->label(),
            self::Medium->value => self::Medium->label(),
            self::High->value => self::High->label(),
        ];
    }
}
