<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

enum ProfitTransactionType: string
{
    case Credit = 'credit';
    case Debit = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::Credit => __('Credit'),
            self::Debit => __('Debit'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public static function rule(): Enum
    {
        return new Enum(self::class);
    }

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

    public static function labelFor(self|int|string|null $value): string
    {
        if ($enum = self::safeFrom($value)) {
            return $enum->label();
        }

        return '-';
    }
}
