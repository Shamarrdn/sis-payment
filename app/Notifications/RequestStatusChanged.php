<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestStatusChanged extends Notification
{
    use Queueable;

    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->data['title'],
            'message' => $this->data['message'],
            'request_type' => $this->data['request_type'],
            'request_id' => $this->data['request_id'],
            'status' => $this->data['status'],
            'action_url' => $this->data['action_url'] ?? null,
        ];
    }
}
