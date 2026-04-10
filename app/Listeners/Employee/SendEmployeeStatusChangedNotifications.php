<?php

declare(strict_types=1);

namespace App\Listeners\Employee;

use App\Events\Employee\EmployeeStatusChanged;
use App\Notifications\Employee\EmployeeStatusChangedNotification;
use App\Services\Notification\LeadershipResolver;
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
        private readonly LeadershipResolver $leadershipResolver,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EmployeeStatusChanged $event): void
    {
        $employee = $event->employee;
        $actor = $event->actor;

        // 1. Resolve leaders for the employee
        $leaders = $this->leadershipResolver->getLeaders($employee);

        // 2. Combine with the employee themselves and filter the actor
        $recipients = $leaders->push($employee)
            ->unique('id')
            ->filter(fn ($user) => $user->id !== $actor?->id);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new EmployeeStatusChangedNotification(
                employee: $employee,
                actor: $actor,
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
