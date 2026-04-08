<?php

declare(strict_types=1);

if (!function_exists('format_money')) {
    /**
     * Format a number into a currency string.
     */
    function format_money(float $amount, string $currency = 'USD'): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('carbon')) {
    /**
     * Shorthand for Carbon parse.
     */
    function carbon(mixed $parseString = null, mixed $tz = null): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::parse($parseString, $tz);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format nullable datetime-like values safely.
     */
    function formatDateTime(mixed $value, string $format = 'd M Y, h:i A'): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        $date = date_create_immutable((string) $value);

        return $date?->format($format);
    }
}
