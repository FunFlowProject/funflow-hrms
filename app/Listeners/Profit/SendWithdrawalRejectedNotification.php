<?php

declare(strict_types=1);

namespace App\Listeners\Profit;

use App\Events\Profit\WithdrawalRejected;
use App\Notifications\Profit\WithdrawalRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendWithdrawalRejectedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(WithdrawalRejected $event): void
    {
        Notification::send(
            $event->withdrawalRequest->user,
            new WithdrawalRejectedNotification(
                withdrawalRequest: $event->withdrawalRequest,
                admin: $event->admin,
            )
        );
    }
}
