<?php

namespace App\Support;

/**
 * Meta Pixel olay kuyruğu — sağlık kategorisi (Health & Wellness) uyumlu.
 *
 * Domain "Sağlık ve zindelik" sınıflandırmasında olduğu için:
 * - Tıbbi ima eden custom isimler YOK (DoctorAppointment, MedicalBooking vb.)
 * - Standart: PageView, ViewContent, Lead (güvenli)
 * - Diğerleri: genel custom (FormSubmit, DemoRequest, SelectPlan…)
 * - Parametreler: plan, source, value, currency — hekim adı / branş YOK
 */
class MetaPixel
{
    public const SESSION_KEY = 'meta_pixel_events';

    public const ONCE_KEY = 'meta_pixel_once';

    /**
     * Uygulama olay adı → Meta'ya giden isim + custom mi?
     *
     * @var array<string, array{event: string, custom: bool}>
     */
    public const EVENT_MAP = [
        // Üst huni — standart
        'PageView' => ['event' => 'PageView', 'custom' => false],
        'ViewContent' => ['event' => 'ViewContent', 'custom' => false],

        // Meta önerisi: Lead standart kullanılabilir
        'Lead' => ['event' => 'Lead', 'custom' => false],
        'CompleteRegistration' => ['event' => 'Lead', 'custom' => false],
        'Contact' => ['event' => 'Lead', 'custom' => false],
        'SubmitApplication' => ['event' => 'FormSubmit', 'custom' => true],

        // Form / randevu talebi — tıbbi isim yok
        'Schedule' => ['event' => 'FormSubmit', 'custom' => true],

        // SaaS funnel — genel isimler
        'AddToCart' => ['event' => 'SelectPlan', 'custom' => true],
        'InitiateCheckout' => ['event' => 'CheckoutStart', 'custom' => true],
        'AddPaymentInfo' => ['event' => 'PaymentInfo', 'custom' => true],
        'Purchase' => ['event' => 'PlanPurchase', 'custom' => true],
        'Subscribe' => ['event' => 'PlanSubscribe', 'custom' => true],
        'StartTrial' => ['event' => 'DemoRequest', 'custom' => true],

        // Arama / konum — genel
        'Search' => ['event' => 'SiteSearch', 'custom' => true],
        'FindLocation' => ['event' => 'SiteSearch', 'custom' => true],
    ];

    /**
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
            'params' => self::sanitizeParams($params, $mapped['event']),
        ];

        session()->put(self::SESSION_KEY, $events);
    }

    /**
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
        if (isset(self::EVENT_MAP[$event])) {
            return self::EVENT_MAP[$event];
        }

        // Bilinmeyen / zaten güvenli custom isimler
        if (in_array($event, ['FormSubmit', 'DemoRequest', 'SelectPlan', 'CheckoutStart', 'PaymentInfo', 'PlanPurchase', 'PlanSubscribe', 'SiteSearch', 'Lead', 'ViewContent', 'PageView'], true)) {
            $custom = ! in_array($event, ['Lead', 'ViewContent', 'PageView'], true);

            return ['event' => $event, 'custom' => $custom];
        }

        // Eski RA_* veya tıbbi isimleri FormSubmit'e düşür
        if (preg_match('/^(RA_|Doctor|Patient|Medical|Health|Appointment)/i', $event)) {
            return ['event' => 'FormSubmit', 'custom' => true];
        }

        return ['event' => 'FormSubmit', 'custom' => true];
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
            $name = (string) $item['event'];
            $custom = (bool) ($item['custom'] ?? true);
            // Eski kuyruk: map tekrar
            if (! array_key_exists('custom', $item) || isset(self::EVENT_MAP[$name])) {
                $mapped = self::mapEvent($name);
                $name = $mapped['event'];
                $custom = $mapped['custom'];
            }
            $out[] = [
                'event' => $name,
                'custom' => $custom,
                'params' => self::sanitizeParams(
                    is_array($item['params'] ?? null) ? $item['params'] : [],
                    $name
                ),
            ];
        }

        return $out;
    }

    /**
     * Sadece genel SaaS parametreleri — hekim adı, branş, muayene tipi YOK.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public static function sanitizeParams(array $params, string $eventName = ''): array
    {
        $allowed = [
            'value', 'currency', 'content_name', 'content_ids', 'content_type',
            'num_items', 'plan', 'source', 'method',
        ];

        $clean = [];
        foreach ($params as $key => $value) {
            if (! is_string($key) || ! in_array($key, $allowed, true)) {
                continue;
            }
            if ($key === 'content_name' && is_string($value)) {
                $value = self::genericLabel($value);
            }
            if ($key === 'plan' && is_string($value)) {
                $value = self::genericLabel($value);
            }
            if (is_array($value)) {
                $clean[$key] = array_values(array_filter(array_map(
                    static fn ($v) => is_scalar($v) ? (string) $v : null,
                    $value
                )));
            } elseif (is_int($value) || is_float($value) || is_string($value)) {
                $clean[$key] = $value;
            }
        }

        // Varsayılanlar
        if (! isset($clean['source'])) {
            $clean['source'] = 'website';
        }
        if (! isset($clean['content_type'])) {
            $clean['content_type'] = 'product';
        }

        // content_name yoksa event'ten genel etiket
        if (! isset($clean['content_name']) && ! isset($clean['plan'])) {
            $clean['content_name'] = match ($eventName) {
                'Lead', 'FormSubmit' => 'form',
                'DemoRequest' => 'trial',
                'SelectPlan', 'CheckoutStart', 'PlanPurchase', 'PlanSubscribe' => 'subscription',
                'ViewContent' => 'page',
                'SiteSearch' => 'search',
                default => 'website',
            };
        }

        return $clean;
    }

    /**
     * Tıbbi / kişi adı içeren metni genel etikete çevir.
     */
    protected static function genericLabel(string $name): string
    {
        $name = trim($name);
        $lower = mb_strtolower($name);

        $map = [
            'başlangıç' => 'starter',
            'baslangic' => 'starter',
            'starter' => 'starter',
            'plus' => 'plus',
            'pro' => 'pro',
            'premium' => 'premium',
            'klinik' => 'clinic',
            'yıllık' => 'yearly',
            'yillik' => 'yearly',
            'aylık' => 'monthly',
            'aylik' => 'monthly',
            'deneme' => 'trial',
            'trial' => 'trial',
            'paket' => 'plan',
            'abonelik' => 'subscription',
            'randevu' => 'booking_form',
            'bekleme' => 'waitlist_form',
            'kayıt' => 'signup_form',
            'kayit' => 'signup_form',
            'eğitim' => 'form',
            'egitim' => 'form',
            'iletişim' => 'contact_form',
            'iletisim' => 'contact_form',
            'hasta' => 'signup_form',
            'hekim' => 'signup_form',
            'booking' => 'booking_form',
            'waitlist' => 'waitlist_form',
            'signup' => 'signup_form',
            'subscription' => 'subscription',
            'product' => 'product',
            'form' => 'form',
            'page' => 'page',
            'search' => 'search',
            'website' => 'website',
            'trial' => 'trial',
            'plan' => 'plan',
        ];

        foreach ($map as $needle => $label) {
            if (str_contains($lower, $needle)) {
                return $label;
            }
        }

        // Tanınmayan uzun metin (hekim adı vb.) → generic
        if (mb_strlen($name) > 24 || preg_match('/\s/', $name)) {
            return 'product';
        }

        // Sadece güvenli kısa slug benzeri etiketler
        if (preg_match('/^[a-z0-9_\-]+$/i', $name)) {
            return mb_strtolower($name);
        }

        return 'product';
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
            'source' => 'website',
        ], $extra);

        // plan alanı paket funnel'ı için
        if (! isset($params['plan']) && $name !== '') {
            $params['plan'] = self::genericLabel($name);
        }

        if ($id !== null && $id !== '') {
            // Sadece sayısal / plan id — isim değil
            $params['content_ids'] = [(string) $id];
        }

        if ($value !== null) {
            $params = array_merge($params, self::money($value, $currency));
        }

        return $params;
    }
}
