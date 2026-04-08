<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

enum SystemRole: string
{
    case Admin = 'admin';
    case Hr = 'hr';
    case Employee = 'employee';

    /**
     * Get the human-readable, translatable label for the enum.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => __('Admin'),
            self::Hr => __('HR'),
            self::Employee => __('Employee'),
        };
    }

    /**
     * Get an array of all enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get an associative array of value => label.
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Generate the Laravel validation rule for this enum.
     */
    public static function rule(): Enum
    {
        return new Enum(self::class);
    }

    /**
     * Safely instantiate from a request value.
     */
    public static function safeFrom(self|int|string|null $value): ?self
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom((string) $value);
    }

    /**
     * Get the label for a specific value, with a safe fallback.
     */
    public static function labelFor(self|int|string|null $value): string
    {
        if ($enum = self::safeFrom($value)) {
            return $enum->label();
        }

        return '-';
    }
}