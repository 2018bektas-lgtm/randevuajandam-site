<?php

namespace App\Jobs;

use App\Models\WhatsappGonderim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp webhook payload'ini isler.
 *
 * Iki tip event beklenir:
 *   - statuses[]  -> gonderim durumu (sent/delivered/read/failed)
 *   - messages[]  -> hastadan gelen mesaj (Model A'da klinige degil bize duser)
 */
class WhatsAppWebhookIsle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 60, 300];

    public function __construct(public array $payload) {}

    public function handle(): void
    {
        $entries = $this->payload['entry'] ?? [];
        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                $value = $change['value'] ?? [];

                foreach (($value['statuses'] ?? []) as $status) {
                    $this->handleStatus($status);
                }

                foreach (($value['messages'] ?? []) as $message) {
                    $this->handleMessage($message, $value);
                }
            }
        }
    }

    /**
     * @param  array  $status  ['id' => wamid, 'status' => sent|delivered|read|failed, ...]
     */
    private function handleStatus(array $status): void
    {
        $wamid = (string) ($status['id'] ?? '');
        if ($wamid === '') {
            return;
        }

        $kayit = WhatsappGonderim::where('wamid', $wamid)->first();
        if (! $kayit) {
            return;
        }

        $meta = strtolower((string) ($status['status'] ?? ''));
        $mapping = [
            'sent'      => WhatsappGonderim::DURUM_GONDERILDI,
            'delivered' => WhatsappGonderim::DURUM_ILETILDI,
            'read'      => WhatsappGonderim::DURUM_OKUNDU,
            'failed'    => WhatsappGonderim::DURUM_HATA,
        ];
        $yerel = $mapping[$meta] ?? null;
        if (! $yerel) {
            return;
        }

        $update = ['durum' => $yerel];
        if ($yerel === WhatsappGonderim::DURUM_HATA) {
            $update['hata'] = json_encode(
                $status['errors'] ?? [],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ) ?: 'unknown';
        }

        $kayit->update($update);
    }

    /**
     * Hastadan gelen mesaj. Model A'da hasta bize yazar; log + ileride panel bildirimi.
     * Simdilik sadece log; klinik paneline dusurme is akisi ayri bir surumde eklenecek.
     */
    private function handleMessage(array $message, array $value): void
    {
        Log::info('WhatsApp gelen mesaj', [
            'from' => $message['from'] ?? null,
            'type' => $message['type'] ?? null,
            'text' => $message['text']['body'] ?? null,
            'to_phone_number_id' => $value['metadata']['phone_number_id'] ?? null,
        ]);
    }
}
