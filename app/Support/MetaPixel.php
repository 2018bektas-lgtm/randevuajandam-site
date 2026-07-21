<?php

namespace App\Support;

/**
 * Meta Pixel olay kuyruğu.
 *
 * Sağlık/kişisel reklam kısıtlarında Schedule, Lead, Purchase vb. standart olaylar
 * bastırılabilir. Bu yüzden kısıtlı olaylar RA_* custom event olarak gönderilir
 * (Events Manager'da custom conversion olarak tanımlanabilir).
 */
class MetaPixel
{
    public const SESSION_KEY = 'meta_pixel_events';

    public const ONCE_KEY = 'meta_pixel_once';

    /**
     * Meta'nın sağlık kategorisinde sık bastırdığı standart olaylar → custom isim.
     *
     * @var array<string, string>
     */
    public const RESTRICTED_TO_CUSTOM = [
        'Schedule' => 'RA_Booking',
        'Lead' => 'RA_Lead',
        'CompleteRegistration' => 'RA_Register',
        'Purchase' => 'RA_Purchase',
        'Subscribe' => 'RA_Subscribe',
        'StartTrial' => 'RA_Trial',
        'InitiateCheckout' => 'RA_Checkout',
        'AddToCart' => 'RA_AddToCart',
        'AddPaymentInfo' => 'RA_PaymentInfo',
        'Contact' => 'RA_Contact',
        'SubmitApplication' => 'RA_Application',
        'FindLocation' => 'RA_FindLocation',
        'Search' => 'RA_Search',
        // ViewContent ve PageView genelde üst huni — standart kalsın
    ];

    /**
     * Olayı oturuma ekle (aynı istek veya bir sonraki sayfa yüklemesinde fire edilir).
     *
     * @param  array<string, mixed>  $params
     */
    public static function queue(string $event, array $params = []): void
    {
        $event = trim($event);
        if ($event === '') {
            return;
        }

        $mapped = self::mapEvent($event);
        $events = session()->get(self::SESSION_KEY, []);
        if (! is_array($events)) {
            $events = [];
        }

        $events[] = [
            'event' => $mapped['event'],
            'custom' => $mapped['custom'],
            'params' => self::sanitizeParams($params),
        ];

        session()->put(self::SESSION_KEY, $events);
    }

    /**
     * Aynı anahtar için yalnızca bir kez kuyruğa al.
     *
     * @param  array<string, mixed>  $params
     */
    public static function queueOnce(string $dedupeKey, string $event, array $params = []): void
    {
        $dedupeKey = trim($dedupeKey);
        if ($dedupeKey === '') {
            self::queue($event, $params);

            return;
        }

        $done = session()->get(self::ONCE_KEY, []);
        if (! is_array($done)) {
            $done = [];
        }

        if (isset($done[$dedupeKey])) {
            return;
        }

        $done[$dedupeKey] = true;
        if (count($done) > 80) {
            $done = array_slice($done, -80, null, true);
        }
        session()->put(self::ONCE_KEY, $done);

        self::queue($event, $params);
    }

    /**
     * @return array{event: string, custom: bool}
     */
    public static function mapEvent(string $event): array
    {
        if (isset(self::RESTRICTED_TO_CUSTOM[$event])) {
            return [
                'event' => self::RESTRICTED_TO_CUSTOM[$event],
                'custom' => true,
            ];
        }

        // Zaten RA_ ile başlıyorsa custom
        if (str_starts_with($event, 'RA_')) {
            return ['event' => $event, 'custom' => true];
        }

        return ['event' => $event, 'custom' => false];
    }

    /**
     * @return list<array{event: string, custom: bool, params: array<string, mixed>}>
     */
    public static function pull(): array
    {
        $events = session()->pull(self::SESSION_KEY, []);

        if (! is_array($events)) {
            return [];
        }

        $out = [];
        foreach ($events as $item) {
            if (! is_array($item) || empty($item['event'])) {
                continue;
            }
            $mapped = self::mapEvent((string) $item['event']);
            // Eski kuyruk formatı: custom bayrağı yoksa yeniden map
            $custom = array_key_exists('custom', $item)
                ? (bool) $item['custom']
                : $mapped['custom'];
            $name = array_key_exists('custom', $item)
                ? (string) $item['event']
                : $mapped['event'];

            $out[] = [
                'event' => $name,
                'custom' => $custom,
                'params' => is_array($item['params'] ?? null)
                    ? self::sanitizeParams($item['params'])
                    : [],
            ];
        }

        return $out;
    }

    /**
     * Sağlık/PHI ima eden alanları temizle; sadece güvenli ölçüm parametreleri.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public static function sanitizeParams(array $params): array
    {
        // Hassas / gereksiz anahtarlar gönderme
        $denyKeys = [
            'status', 'email', 'phone', 'em', 'ph', 'fn', 'ln',
            'external_id', 'content_category', 'predicted_ltv',
        ];

        $clean = [];
        foreach ($params as $key => $value) {
            if (! is_string($key) || $key === '' || in_array($key, $denyKeys, true)) {
                continue;
            }
            // content_name'den tıbbi branş ima eden uzun metinleri kısalt / genelle
            if ($key === 'content_name' && is_string($value)) {
                $value = self::genericContentName($value);
            }
            if (is_array($value)) {
                $clean[$key] = array_values(array_map(
                    static fn ($v) => is_scalar($v) || $v === null ? $v : (string) $v,
                    $value
                ));
            } elseif (is_bool($value) || is_int($value) || is_float($value) || is_string($value) || $value === null) {
                $clean[$key] = $value;
            } else {
                $clean[$key] = (string) $value;
            }
        }

        // content_type her zaman product (e-ticaret benzeri SaaS)
        if (! isset($clean['content_type'])) {
            $clean['content_type'] = 'product';
        }

        return $clean;
    }

    protected static function genericContentName(string $name): string
    {
        $name = trim($name);
        // Branş/hekim adı yerine genel etiket
        $generics = [
            'randevu' => 'booking',
            'misafir' => 'booking',
            'bekleme' => 'waitlist',
            'kayıt' => 'signup',
            'kayit' => 'signup',
            'hasta' => 'signup',
            'hekim' => 'signup',
            'klinik' => 'signup',
            'paket' => 'subscription',
            'deneme' => 'trial',
            'eğitim' => 'lead',
            'egitim' => 'lead',
            'iletişim' => 'contact',
            'iletisim' => 'contact',
        ];
        $lower = mb_strtolower($name);
        foreach ($generics as $needle => $label) {
            if (str_contains($lower, $needle)) {
                return $label;
            }
        }

        // Bilinmeyen adları ID'ye indirgeme: generic product
        if (mb_strlen($name) > 40) {
            return 'product';
        }

        return $name;
    }

    /**
     * @return array{value: float, currency: string}
     */
    public static function money(float $value, string $currency = 'TRY'): array
    {
        return [
            'value' => round(max(0, $value), 2),
            'currency' => $currency !== '' ? strtoupper($currency) : 'TRY',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function content(
        string $name,
        string $type = 'product',
        ?string $id = null,
        ?float $value = null,
        string $currency = 'TRY',
        array $extra = []
    ): array {
        $params = array_merge([
            'content_name' => $name,
            'content_type' => $type,
        ], $extra);

        if ($id !== null && $id !== '') {
            $params['content_ids'] = [(string) $id];
        }

        if ($value !== null) {
            $params = array_merge($params, self::money($value, $currency));
        }

        return $params;
    }
}
