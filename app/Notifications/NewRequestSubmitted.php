<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRequestSubmitted extends Notification
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
            'student_name' => $this->data['student_name'],
            'request_type' => $this->data['request_type'],
            'request_id' => $this->data['request_id'],
            'action_url' => $this->data['action_url'] ?? null,
        ];
    }
}
