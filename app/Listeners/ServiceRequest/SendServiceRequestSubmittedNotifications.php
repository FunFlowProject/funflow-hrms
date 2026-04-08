<?php

declare(strict_types=1);

namespace App\Listeners\ServiceRequest;

use App\Events\ServiceRequest\ServiceRequestSubmitted;
use App\Notifications\ServiceRequest\ServiceRequestSubmittedNotification;
use App\Services\ServiceRequest\ServiceRequestNotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendServiceRequestSubmittedNotifications implements ShouldQueue
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
    public function handle(ServiceRequestSubmitted $event): void
    {
        $recipients = $this->recipientResolver->resolveForSubmission();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ServiceRequestSubmittedNotification(
                serviceRequest: $event->serviceRequest,
                actor: $event->actor,
                action: $event->action,
                note: $event->note,
                details: $event->details,
            )
        );
    }
}
