<?php

declare(strict_types=1);

namespace App\Notifications\ServiceRequest;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ServiceRequestStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param array<string, mixed> $details
     */
    public function __construct(
        public readonly ServiceRequest $serviceRequest,
        public readonly ?User $actor,
        public readonly ?ServiceRequestStatus $fromStatus,
        public readonly ServiceRequestStatus $toStatus,
        public readonly string $action,
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
        $subject = __('Service Request Updated: :service', [
            'service' => $this->serviceRequest->service_name_snapshot,
        ]);

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notifications.service-request-status-changed', [
                'subject' => $subject,
                'recipientName' => $notifiable instanceof User ? $notifiable->full_name : null,
                'serviceRequest' => $this->serviceRequest,
                'serviceName' => $this->serviceRequest->service_name_snapshot,
                'serviceCategory' => $this->serviceRequest->service_category_snapshot,
                'fromStatusLabel' => $this->fromStatus?->label(),
                'toStatusLabel' => $this->toStatus->label(),
                'actionLabel' => $this->actionLabel(),
                'note' => $this->note,
                'rejectionReason' => $this->serviceRequest->rejection_reason,
                'fulfillmentNote' => $this->serviceRequest->fulfillment_note,
                'actorName' => $this->actor?->full_name,
                'requestUrl' => route('service-requests.index'),
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
            'type' => 'service_request_status_changed',
            'service_request_id' => $this->serviceRequest->id,
            'service_name' => $this->serviceRequest->service_name_snapshot,
            'service_category' => $this->serviceRequest->service_category_snapshot,
            'requester_id' => $this->serviceRequest->requester_id,
            'from_status' => $this->fromStatus?->value,
            'from_status_label' => $this->fromStatus?->label(),
            'to_status' => $this->toStatus->value,
            'to_status_label' => $this->toStatus->label(),
            'action' => $this->action,
            'action_label' => $this->actionLabel(),
            'note' => $this->note,
            'actor_id' => $this->actor?->id,
            'actor_name' => $this->actor?->full_name,
            'details' => $this->details,
            'url' => route('service-requests.index'),
            'created_at' => now()->toDateTimeString(),
        ];
    }

    private function actionLabel(): string
    {
        return Str::of($this->action)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}
