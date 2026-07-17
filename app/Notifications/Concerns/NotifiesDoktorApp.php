<?php

namespace App\Notifications\Concerns;

use App\Notifications\Channels\ExpoPushChannel;

/**
 * Database + Expo push for doctor mobile app notifications.
 * Expects toArray() to include title/body (or baslik/mesaj fallbacks).
 */
trait NotifiesDoktorApp
{
    /**
     * @return array<int, string|class-string>
     */
    protected function doktorAppChannels(array $extra = []): array
    {
        return array_values(array_unique(array_merge(
            ['database', ExpoPushChannel::class],
            $extra
        )));
    }

    /**
     * @return array{title: string, body: string, data: array<string, mixed>}
     */
    public function toExpoPush(object $notifiable): array
    {
        $arr = method_exists($this, 'toArray') ? $this->toArray($notifiable) : [];
        if (! is_array($arr)) {
            $arr = [];
        }

        $title = (string) ($arr['title'] ?? $arr['baslik'] ?? 'Randevu Ajandam');
        $body = (string) ($arr['body'] ?? $arr['mesaj'] ?? '');

        return [
            'title' => $title,
            'body' => $body,
            'data' => array_merge($arr, [
                'type' => (string) ($arr['type'] ?? class_basename(static::class)),
            ]),
        ];
    }
}
