<?php

declare(strict_types=1);

namespace App\Listeners\Profit;

use App\Events\Profit\ProfitDistributed;
use App\Notifications\Profit\ProfitDistributedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendProfitDistributedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(ProfitDistributed $event): void
    {
        Notification::send(
            $event->employee,
            new ProfitDistributedNotification(
                transaction: $event->transaction,
                admin: $event->admin,
            )
        );
    }
}
