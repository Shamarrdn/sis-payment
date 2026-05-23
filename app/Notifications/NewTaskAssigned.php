<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTaskAssigned extends Notification
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
            'student_name' => $this->data['student_name'] ?? null,
            'ticket_id' => $this->data['ticket_id'] ?? null,
            'priority' => $this->data['priority'] ?? null,
            'category' => $this->data['category'] ?? null,
            'action_url' => $this->data['action_url'] ?? null,
        ];
    }
}
