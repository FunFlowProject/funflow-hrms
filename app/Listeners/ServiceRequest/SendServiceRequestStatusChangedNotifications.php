<?php

declare(strict_types=1);

namespace App\Listeners\ServiceRequest;

use App\Events\ServiceRequest\ServiceRequestStatusChanged;
use App\Notifications\ServiceRequest\ServiceRequestStatusChangedNotification;
use App\Services\Notification\LeadershipResolver;
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
        private readonly LeadershipResolver $leadershipResolver,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ServiceRequestStatusChanged $event): void
    {
        $serviceRequest = $event->serviceRequest;
        $actor = $event->actor;

        // 1. Resolve Requester
        $requester = $serviceRequest->requester;

        // 2. Resolve Leaders of the requester
        $leaders = $this->leadershipResolver->getLeaders($requester);

        // 3. Combine and filter out the actor
        $recipients = collect($requester ? [$requester] : [])
            ->concat($leaders)
            ->unique('id')
            ->filter(fn ($user) => $user->id !== $actor?->id);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ServiceRequestStatusChangedNotification(
                serviceRequest: $serviceRequest,
                actor: $actor,
                fromStatus: $event->fromStatus,
                toStatus: $event->toStatus,
                action: $event->action,
                note: $event->note,
                details: $event->details,
            )
        );
    }
}
