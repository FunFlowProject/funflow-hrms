<?php

declare(strict_types=1);

namespace App\Notifications\Employee;

use App\Enums\EmployeeStatus;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class EmployeeAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param array<int, array<string, mixed>> $assignmentSnapshot
     * @param array<string, mixed> $details
     */
    public function __construct(
        public readonly User $employee,
        public readonly ?User $actor = null,
        public readonly array $assignmentSnapshot = [],
        public readonly EmployeeStatus $status = EmployeeStatus::Pending,
        public readonly string $action = 'employee_created',
        public readonly ?string $note = null,
        public readonly array $details = [],
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = __('New Employee Added: :name', ['name' => $this->employee->full_name]);

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notifications.employee-added', [
                'subject' => $subject,
                'recipientName' => $notifiable instanceof User ? $notifiable->full_name : null,
                'employee' => $this->employee,
                'employeeUsername' => $this->employee->username,
                'initialPassword' => $this->isEmployeeRecipient($notifiable) ? $this->initialPassword() : null,
                'statusLabel' => $this->status->label(),
                'actionLabel' => $this->actionLabel(),
                'assignmentSnapshot' => $this->formatAssignments($this->assignmentSnapshot),
                'note' => $this->note,
                'actorName' => $this->actor?->full_name,
                'employeeUrl' => route('employees.show', ['employee' => $this->employee->id]),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'employee_added',
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->full_name,
            'employee_email' => $this->employee->email,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'action' => $this->action,
            'action_label' => $this->actionLabel(),
            'note' => $this->note,
            'actor_id' => $this->actor?->id,
            'actor_name' => $this->actor?->full_name,
            'assignments' => $this->formatAssignments($this->assignmentSnapshot),
            'details' => $this->databaseDetails(),
            'url' => route('employees.show', ['employee' => $this->employee->id]),
            'created_at' => now()->toDateTimeString(),
        ];
    }

    private function isEmployeeRecipient(object $notifiable): bool
    {
        return $notifiable instanceof User && $notifiable->id === $this->employee->id;
    }

    private function initialPassword(): ?string
    {
        $value = $this->details['initial_password'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function databaseDetails(): array
    {
        $details = $this->details;
        unset($details['initial_password']);

        return $details;
    }

    /**
     * @param array<int, array<string, mixed>> $assignments
     * @return array<int, array<string, mixed>>
     */
    private function formatAssignments(array $assignments): array
    {
        return array_values(array_map(static function (array $assignment): array {
            return [
                'sub_company_id' => $assignment['sub_company_id'] ?? null,
                'sub_company_name' => $assignment['sub_company_name'] ?? null,
                'squad_id' => $assignment['squad_id'] ?? null,
                'squad_name' => $assignment['squad_name'] ?? null,
                'hierarchy_id' => $assignment['hierarchy_id'] ?? null,
                'hierarchy_title' => $assignment['hierarchy_title'] ?? null,
            ];
        }, $assignments));
    }

    private function actionLabel(): string
    {
        return Str::of($this->action)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}
