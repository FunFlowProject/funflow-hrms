<?php

declare(strict_types=1);

namespace App\Listeners\Employee;

use App\Events\Employee\EmployeeStatusChanged;
use App\Notifications\Employee\EmployeeStatusChangedNotification;
use App\Services\Employee\EmployeeNotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendEmployeeStatusChangedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly EmployeeNotificationRecipientResolver $recipientResolver,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EmployeeStatusChanged $event): void
    {
        $recipients = $this->recipientResolver->resolveForEmployee($event->employee);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new EmployeeStatusChangedNotification(
                employee: $event->employee,
                actor: $event->actor,
                fromStatus: $event->fromStatus,
                toStatus: $event->toStatus,
                action: $event->action,
                note: $event->note,
                assignmentSnapshot: $event->assignmentSnapshot,
                details: $event->details,
            )
        );
    }
}
