<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    protected $details;

    /**
     * Create a new notification instance.
     *
     * @param array $details ['title', 'message', 'action_url', 'type']
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->id,
            'title' => $this->details['title'] ?? 'Universal notification',
            'message' => $this->details['message'] ?? '',
            'action_url' => $this->details['action_url'] ?? '#',
            'type' => $this->details['type'] ?? 'info', // info, success, warning, danger
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
