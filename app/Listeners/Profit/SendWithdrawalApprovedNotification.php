<?php

declare(strict_types=1);

namespace App\Listeners\Profit;

use App\Events\Profit\WithdrawalApproved;
use App\Notifications\Profit\WithdrawalApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendWithdrawalApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(WithdrawalApproved $event): void
    {
        Notification::send(
            $event->withdrawalRequest->user,
            new WithdrawalApprovedNotification(
                withdrawalRequest: $event->withdrawalRequest,
                transaction: $event->transaction,
                admin: $event->admin,
            )
        );
    }
}
