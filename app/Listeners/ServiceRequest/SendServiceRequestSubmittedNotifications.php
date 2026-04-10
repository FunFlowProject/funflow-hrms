<?php

declare(strict_types=1);

namespace App\Listeners\ServiceRequest;

use App\Events\ServiceRequest\ServiceRequestSubmitted;
use App\Notifications\ServiceRequest\ServiceRequestSubmittedNotification;
use App\Services\Notification\LeadershipResolver;
use App\Models\User;
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
        private readonly LeadershipResolver $leadershipResolver,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ServiceRequestSubmitted $event): void
    {
        $serviceRequest = $event->serviceRequest;
        $actor = $event->actor;

        // 1. Resolve Leaders of the requester
        $leaders = $this->leadershipResolver->getLeaders($serviceRequest->requester);

        // 2. Resolve Group Management (HR/Admins)
        $management = User::permission('service-requests.manage')
            ->select(['id', 'full_name', 'email'])
            ->get();

        // 3. Combine and filter out the actor
        $recipients = $leaders->concat($management)
            ->unique('id')
            ->filter(fn ($user) => $user->id !== $actor?->id);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ServiceRequestSubmittedNotification(
                serviceRequest: $serviceRequest,
                actor: $actor,
                action: $event->action,
                note: $event->note,
                details: $event->details,
            )
        );
    }
}
