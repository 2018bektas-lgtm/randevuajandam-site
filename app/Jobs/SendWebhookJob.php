<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventName;
    protected $payload;
    protected $doktorId;
    protected $klinikId;

    /**
     * Create a new job instance.
     *
     * @param string $eventName
     * @param array $payload
     * @param int|null $doktorId
     * @param int|null $klinikId
     */
    public function __construct(string $eventName, array $payload, ?int $doktorId = null, ?int $klinikId = null)
    {
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->doktorId = $doktorId;
        $this->klinikId = $klinikId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Alıcı webhook uç noktalarını bul (doktora veya kliniğe ait aktif webhook'lar)
        $query = WebhookEndpoint::where('aktif', true);

        if ($this->doktorId && $this->klinikId) {
            $query->where(function ($q) {
                $q->where('doktor_id', $this->doktorId)
                  ->orWhere('klinik_id', $this->klinikId);
            });
        } elseif ($this->doktorId) {
            $query->where('doktor_id', $this->doktorId);
        } elseif ($this->klinikId) {
            $query->where('klinik_id', $this->klinikId);
        } else {
            return;
        }

        $endpoints = $query->get();

        foreach ($endpoints as $endpoint) {
            // Olay abonelik kontrolü
            $subscribedEvents = $endpoint->events;
            $shouldSend = is_null($subscribedEvents)
                || in_array('*', $subscribedEvents)
                || in_array($this->eventName, $subscribedEvents);

            if (! $shouldSend) {
                continue;
            }

            $url = (string) $endpoint->url;
            if ($url === '' || empty($endpoint->secret_key)) {
                Log::warning('Webhook skipped: empty url or secret', ['id' => $endpoint->id ?? null]);
                continue;
            }

            // Production: only HTTPS external targets (block cleartext + obvious SSRF locals)
            if (app()->environment('production')) {
                if (! str_starts_with($url, 'https://')) {
                    Log::warning("Webhook skipped (HTTPS required): {$url}");
                    continue;
                }
                $host = parse_url($url, PHP_URL_HOST) ?: '';
                if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true)
                    || str_starts_with($host, '10.')
                    || str_starts_with($host, '192.168.')
                    || preg_match('/^172\.(1[6-9]|2\d|3[0-1])\./', $host)) {
                    Log::warning("Webhook skipped (private host): {$url}");
                    continue;
                }
            }

            $timestamp = time();
            $body = json_encode([
                'event' => $this->eventName,
                'timestamp' => $timestamp,
                'data' => $this->payload,
            ]);

            $payloadToSign = $timestamp.$body;
            $signature = hash_hmac('sha256', $payloadToSign, $endpoint->secret_key);

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Event' => $this->eventName,
                    'X-Webhook-Signature' => $signature,
                    'X-Timestamp' => $timestamp,
                ])
                    ->timeout(10)
                    ->post($url, json_decode($body, true));

                if (! $response->successful()) {
                    Log::warning("Webhook dispatch failed to URL: {$url}. Status: {$response->status()}");
                }
            } catch (\Exception $e) {
                Log::error("Webhook error for URL {$url}: ".$e->getMessage());
            }
        }
    }
}
