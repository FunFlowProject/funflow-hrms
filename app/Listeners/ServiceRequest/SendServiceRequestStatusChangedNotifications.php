<?php

declare(strict_types=1);

namespace App\Listeners\ServiceRequest;

use App\Events\ServiceRequest\ServiceRequestStatusChanged;
use App\Notifications\ServiceRequest\ServiceRequestStatusChangedNotification;
use App\Services\ServiceRequest\ServiceRequestNotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendServiceRequestStatusChangedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly ServiceRequestNotificationRecipientResolver $recipientResolver,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ServiceRequestStatusChanged $event): void
    {
        $recipients = $this->recipientResolver->resolveForStatusChange($event->serviceRequest);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ServiceRequestStatusChangedNotification(
                serviceRequest: $event->serviceRequest,
                actor: $event->actor,
                fromStatus: $event->fromStatus,
                toStatus: $event->toStatus,
                action: $event->action,
                note: $event->note,
                details: $event->details,
            )
        );
    }
}
