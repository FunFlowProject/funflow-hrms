<?php

declare(strict_types=1);

namespace App\Listeners\Employee;

use App\Events\Employee\EmployeeCreated;
use App\Notifications\Employee\EmployeeAddedNotification;
use App\Services\Employee\EmployeeNotificationRecipientResolver;
use Illuminate\Support\Facades\Notification;

class SendEmployeeCreatedNotifications
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly EmployeeNotificationRecipientResolver $recipientResolver,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EmployeeCreated $event): void
    {
        $recipients = $this->recipientResolver->resolveForEmployee($event->employee);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::sendNow(
            $recipients,
            new EmployeeAddedNotification(
                employee: $event->employee,
                actor: $event->actor,
                assignmentSnapshot: $event->assignmentSnapshot,
                status: $event->status,
                action: $event->action,
                note: $event->note,
                details: $event->details,
            )
        );
    }
}
