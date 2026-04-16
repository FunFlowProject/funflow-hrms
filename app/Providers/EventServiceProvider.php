<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Profit\ProfitDistributed;
use App\Events\Profit\WithdrawalApproved;
use App\Events\Profit\WithdrawalRejected;
use App\Listeners\Profit\SendProfitDistributedNotification;
use App\Listeners\Profit\SendWithdrawalApprovedNotification;
use App\Listeners\Profit\SendWithdrawalRejectedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProfitDistributed::class => [
            SendProfitDistributedNotification::class,
        ],
        WithdrawalApproved::class => [
            SendWithdrawalApprovedNotification::class,
        ],
        WithdrawalRejected::class => [
            SendWithdrawalRejectedNotification::class,
        ],
    ];
}
