<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPaymentOrDue extends Notification
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
            'amount' => $this->data['amount'] ?? null,
            'service_name' => $this->data['service_name'] ?? null,
            'reference_number' => $this->data['reference_number'] ?? null,
            'action_url' => $this->data['action_url'] ?? null,
        ];
    }
}
