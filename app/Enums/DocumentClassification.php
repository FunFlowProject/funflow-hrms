<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentClassification: string
{
    case Public = 'public';
    case InternalUseOnly = 'internal_use_only';
    case Confidential = 'confidential';

    public function label(): string
    {
        return match ($this) {
            self::Public => __('Public'),
            self::InternalUseOnly => __('Internal Use Only'),
            self::Confidential => __('Confidential'),
        };
    }
}
