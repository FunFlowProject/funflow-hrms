<?php

declare(strict_types=1);

namespace App\Listeners\Employee;

use App\Events\Employee\EmployeeCreated;
use App\Notifications\Employee\EmployeeAddedNotification;
use App\Services\Notification\LeadershipResolver;
use Illuminate\Support\Facades\Notification;

class SendEmployeeCreatedNotifications
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly LeadershipResolver $leadershipResolver,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EmployeeCreated $event): void
    {
        $employee = $event->employee;
        $actor = $event->actor;

        // 1. Resolve leaders for the new employee
        $leaders = $this->leadershipResolver->getLeaders($employee);

        // 2. Combine with the employee themselves and filter the actor
        $recipients = $leaders->push($employee)
            ->unique('id')
            ->filter(fn ($user) => $user->id !== $actor?->id);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::sendNow(
            $recipients,
            new EmployeeAddedNotification(
                employee: $employee,
                actor: $actor,
                assignmentSnapshot: $event->assignmentSnapshot,
                status: $event->status,
                action: $event->action,
                note: $event->note,
                details: $event->details,
            )
        );
    }
}
