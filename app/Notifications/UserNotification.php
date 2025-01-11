<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;
    public $actor;
    public $object;
    public $activity;

    /**
     * Create a new notification instance.
     */
    public function __construct($type, $actor, $object, $activity = null)
    {
        $this->type = $type;
        $this->actor = $actor;
        $this->object = $object;
        $this->activity = $activity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'actor' => $this->actor,
            'object' => $this->object,
            'activity' => $this->activity,
        ];
    }

    public function toBroadcast ($notifiable)
    {
        // we don't really need to broadcast any information
        return [
            "notification_type" => $this->type,
            "actor" => $this->actor,
            "object" => $this->object,
            "activity" => $this->activity,
        ];
    }
}
