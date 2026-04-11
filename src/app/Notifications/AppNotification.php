<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $type = 'success',
        public ?string $href = null,
        public ?string $icon = null
    ) {}

    public function via($notifiable): array
    {
        return ['database']; // Сохраняем уведомление только в БД
    }

    public function toArray($notifiable): array
    {
        return[
            'message' => $this->message,
            'type'    => $this->type,
            'href'    => $this->href,
            'icon'    => $this->icon,
        ];
    }
}