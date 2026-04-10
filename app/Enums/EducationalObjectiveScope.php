<?php

declare(strict_types=1);

namespace App\Enums;

enum EducationalObjectiveScope: string
{
    case Company = 'company';
    case SubCompany = 'sub_company';
    case Squad = 'squad';
    case Individual = 'individual';

    public function label(): string
    {
        return match ($this) {
            self::Company => __('Company-wide'),
            self::SubCompany => __('Sub-Company'),
            self::Squad => __('Squad'),
            self::Individual => __('Individual'),
        };
    }
}
