<?php

declare(strict_types=1);

namespace App\Notifications\Profit;

use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly WithdrawalRequest $withdrawalRequest,
        public readonly User $admin,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = __('Withdrawal Request Rejected');

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notifications.withdrawal-rejected', [
                'subject' => $subject,
                'recipientName' => $notifiable instanceof User ? $notifiable->full_name : null,
                'withdrawalRequest' => $this->withdrawalRequest,
                'amount' => format_money((float) $this->withdrawalRequest->amount),
                'reason' => $this->withdrawalRequest->rejection_reason,
                'adminName' => $this->admin->full_name,
                'requestUrl' => route('my-profit.index'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_rejected',
            'withdrawal_request_id' => $this->withdrawalRequest->id,
            'amount' => (float) $this->withdrawalRequest->amount,
            'amount_formatted' => format_money((float) $this->withdrawalRequest->amount),
            'reason' => $this->withdrawalRequest->rejection_reason,
            'performed_by' => $this->admin->id,
            'performed_by_name' => $this->admin->full_name,
            'url' => route('my-profit.index'),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
