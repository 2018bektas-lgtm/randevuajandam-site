<?php

namespace App\Jobs;

use App\Models\Klinik;
use App\Models\WhatsappGonderim;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * WhatsApp Business API uzerinden onayli sablon gonderir.
 * Kota bitti / entegrasyon kapali / Meta hatasi tekrarli olursa
 * $smsFallbackText verildiyse ayni mesaji SMS ile dener.
 */
class WhatsAppGonder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    public $tries = 3;

    /** @var array<int, int> */
    public $backoff = [30, 120, 600];

    /** @var int */
    public $timeout = 60;

    public function __construct(
        public ?int $klinikId,
        public string $telefon,
        public string $sablon,
        public array $degiskenler = [],
        public ?int $hastaId = null,
        public ?int $doktorId = null,
        public ?string $smsFallbackText = null,
        public string $kategori = 'utility',
    ) {}

    public function handle(): void
    {
        // 1) Global kapali? -> SMS'e dus (mumkunse) ve cik
        if (! config('whatsapp.enabled', false)) {
            $this->smsFallback('whatsapp_disabled');
            return;
        }

        $klinik = $this->klinikId ? Klinik::find($this->klinikId) : null;

        // 2) Klinik varsa kota kontrolu
        if ($klinik && $klinik->whatsappKotaKaldi() <= 0) {
            $this->smsFallback('kota_bitti');
            return;
        }

        // 3) Gonderim kaydi (idempotency icin update() ile ilerleriz)
        $kayit = WhatsappGonderim::create([
            'klinik_id' => $klinik?->id,
            'doktor_id' => $this->doktorId,
            'hasta_id'  => $this->hastaId,
            'telefon'   => $this->telefon,
            'sablon'    => $this->sablon,
            'kategori'  => $this->kategori,
            'durum'     => WhatsappGonderim::DURUM_KUYRUKTA,
        ]);

        try {
            $cevap = (new WhatsAppService($klinik))
                ->sablonGonder($this->telefon, $this->sablon, $this->degiskenler);

            $wamid = $cevap['messages'][0]['id'] ?? null;
            $kayit->update([
                'wamid' => $wamid,
                'durum' => WhatsappGonderim::DURUM_GONDERILDI,
            ]);
        } catch (Throwable $e) {
            $kayit->update([
                'durum' => WhatsappGonderim::DURUM_HATA,
                'hata' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            // Son deneme ise sessiz SMS fallback tetikle; oncekilerde retry devam etsin.
            if ($this->attempts() >= $this->tries) {
                $this->smsFallback('meta_hata', $kayit);
                return;
            }

            throw $e;
        }
    }

    /**
     * Job final olarak basarisiz olursa (retry'lar bitti) da SMS'e dus.
     */
    public function failed(?Throwable $e): void
    {
        $this->smsFallback('job_failed');
        if ($e) {
            Log::warning('WhatsAppGonder job final basarisiz', [
                'telefon' => $this->telefon,
                'sablon' => $this->sablon,
                'hata' => $e->getMessage(),
            ]);
        }
    }

    private function smsFallback(string $sebep, ?WhatsappGonderim $kayit = null): void
    {
        if ($this->smsFallbackText === null || trim($this->smsFallbackText) === '') {
            return;
        }

        try {
            $ok = app(SmsService::class)->send($this->telefon, $this->smsFallbackText);
            if ($ok && $kayit) {
                $kayit->update(['durum' => WhatsappGonderim::DURUM_SMS_FALLBACK]);
            }
        } catch (Throwable $e) {
            Log::warning('WhatsApp SMS fallback hatasi', [
                'sebep' => $sebep,
                'telefon' => $this->telefon,
                'hata' => $e->getMessage(),
            ]);
        }
    }
}
