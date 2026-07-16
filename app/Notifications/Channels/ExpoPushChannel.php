<?php

namespace App\Notifications\Channels;

use App\Models\Doktor;
use App\Services\ExpoPushService;
use Illuminate\Notifications\Notification;

class ExpoPushChannel
{
    public function __construct(
        protected ExpoPushService $push
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable instanceof Doktor) {
            return;
        }

        if (! method_exists($notification, 'toExpoPush')) {
            return;
        }

        /** @var array{title?: string, body?: string, data?: array}|null $payload */
        $payload = $notification->toExpoPush($notifiable);
        if (! is_array($payload)) {
            return;
        }

        $title = (string) ($payload['title'] ?? 'Randevu Ajandam');
        $body = (string) ($payload['body'] ?? '');
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $this->push->sendToDoktor($notifiable, $title, $body, $data);
    }
}
