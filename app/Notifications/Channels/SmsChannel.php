<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(
        protected SmsService $smsService
    ) {}

    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        // Check if the notifiable has a telephone number
        $phone = $notifiable->telefon;

        if (empty($phone)) {
            return;
        }

        // Get the SMS message content
        $message = $notification->toSms($notifiable);

        if (empty($message)) {
            return;
        }

        // Send the SMS
        $this->smsService->send($phone, $message);
    }
}
