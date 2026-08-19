<?php

namespace App\Services;

use App\Models\Doktor;
use App\Models\Klinik;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * WhatsApp Business Cloud API istemcisi.
 *
 * Tenant hicligi (null) = Model A (config('whatsapp.default')).
 * Doktor / Klinik verirse whatsappAyari() ile Model B ozel numarasi kullanilir.
 * Array de gecilebilir — dogrudan config sozlugu.
 */
class WhatsAppService
{
    /**
     * @param  Klinik|Doktor|array|null  $tenant
     */
    public function __construct(private Klinik|Doktor|array|null $tenant = null) {}

    /**
     * Onayli sablon gonderir.
     *
     * @param  string   $telefon   Herhangi bir formatta TR numarasi (5xx, 0 5xx, +90 5xx...)
     * @param  string   $sablon    Meta panelinde onayli sablon adi (ornek: randevu_hatirlatma)
     * @param  array    $degiskenler  Sablon body parameter'lari (siralama {{1}}, {{2}}, ...)
     * @param  ?string  $languageOverride  Sablon farkli bir dilde onaylandiysa (default tr)
     *
     * @throws RuntimeException Config eksik ya da API hata donerse.
     */
    public function sablonGonder(
        string $telefon,
        string $sablon,
        array $degiskenler = [],
        ?string $languageOverride = null,
    ): array {
        $ayar = $this->ayar();
        $token = (string) ($ayar['token'] ?? '');
        $phoneNumberId = (string) ($ayar['phone_number_id'] ?? '');

        if ($token === '' || $phoneNumberId === '') {
            throw new RuntimeException('WhatsApp yapilandirmasi eksik (token / phone_number_id).');
        }

        $sur = config('whatsapp.api_version');
        $base = rtrim((string) config('whatsapp.base_url'), '/');
        $url = "{$base}/{$sur}/{$phoneNumberId}/messages";
        $language = $languageOverride ?: (string) config('whatsapp.language', 'tr');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalizeTelefon($telefon),
            'type'              => 'template',
            'template' => [
                'name'     => $sablon,
                'language' => ['code' => $language],
            ],
        ];

        if ($degiskenler !== []) {
            $payload['template']['components'] = [[
                'type' => 'body',
                'parameters' => array_map(
                    fn ($v) => ['type' => 'text', 'text' => (string) $v],
                    array_values($degiskenler),
                ),
            ]];
        }

        $response = Http::withToken($token)
            ->timeout(20)
            ->retry(2, 500, throw: false)
            ->acceptJson()
            ->post($url, $payload);

        $this->throwIfError($response);

        return $response->json() ?: [];
    }

    /**
     * TR telefon numarasini Meta'nin bekledigi E.164 formatina (basinda + olmadan) cevirir.
     */
    public function normalizeTelefon(string $t): string
    {
        $digits = preg_replace('/\D/', '', $t) ?? '';

        // "0090..." -> "90..."
        if (str_starts_with($digits, '0090')) {
            $digits = substr($digits, 2);
        }

        // "0 5xx xxx xx xx" (11 hane) -> "5xx..."
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        // Ulke kodu yoksa 5 ile basliyor ve 10 hane ise 90 ekle
        if (strlen($digits) === 10 && str_starts_with($digits, '5')) {
            $digits = '90' . $digits;
        }

        return $digits;
    }

    /**
     * @return array{token: ?string, phone_number_id: ?string, waba_id: ?string}
     */
    private function ayar(): array
    {
        if ($this->tenant instanceof Doktor || $this->tenant instanceof Klinik) {
            return $this->tenant->whatsappAyari();
        }

        if (is_array($this->tenant) && $this->tenant !== []) {
            return $this->tenant;
        }

        return (array) config('whatsapp.default');
    }

    private function throwIfError(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $json = $response->json() ?: [];
        $error = $json['error']['message'] ?? $response->body();
        $code = $json['error']['code'] ?? $response->status();

        throw new RuntimeException(
            "WhatsApp API hata (code {$code}): {$error}",
            (int) ($response->status() ?: 500),
        );
    }
}
