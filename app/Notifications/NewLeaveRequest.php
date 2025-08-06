<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeaveRequest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $leave;

    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $user = $this->leave->user;
        return (new MailMessage)
                    ->subject('New Leave Request')
                    ->line('A new leave request has been submitted by ' . $user->name . '.')
                    ->line('Reason: ' . $this->leave->reason)
                    ->line('From: ' . $this->leave->start_date . ' to ' . $this->leave->end_date)
                    ->action('View Leave Request', url('/leaves/' . $this->leave->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'leave_id' => $this->leave->id,
            'user_name' => $this->leave->user->name,
            'reason' => $this->leave->reason,
        ];
    }
}
