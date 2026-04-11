<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountUpdatedNotification extends Notification
{
    use \Illuminate\Bus\Queueable;

    /**
     * The changes made to the account.
     *
     * @var array
     */
    public $changes;

    /**
     * Create a notification instance.
     *
     * @param  array  $changes  Format: ['Field Name' => ['old' => '...', 'new' => '...']]
     * @return void
     */
    public function __construct(array $changes)
    {
        $this->changes = $changes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject(\Illuminate\Support\Facades\Lang::get('Account Details Updated'))
            ->view('emails.notifications.account-updated', [
                'subject' => \Illuminate\Support\Facades\Lang::get('Account Details Updated'),
                'changes' => $this->changes,
                'user' => $notifiable,
                'mailMessage' => (new \Illuminate\Notifications\Messages\MailMessage),
            ]);
    }
}
