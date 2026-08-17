<?php

namespace App\Services;

use App\Models\Doktor;
use App\Support\PaketYetki;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsistanService
{
    private const GUNLUK_LIMIT = 100;

    private const FONKSIYON_TANIMLARI = [
        [
            'name'        => 'randevu_listele',
            'description' => 'Hekimin belirli tarih aralığındaki randevularını listeler.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'tarih_baslangic' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                    'tarih_bitis'     => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                    'durum'           => ['type' => 'string', 'description' => 'Opsiyonel: beklemede, onaylandi, tamamlandi, iptal'],
                ],
                'required' => ['tarih_baslangic', 'tarih_bitis'],
            ],
        ],
        [
            'name'        => 'bos_saat_bul',
            'description' => 'Belirli bir günde müsait randevu saatlerini listeler.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'tarih' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                ],
                'required' => ['tarih'],
            ],
        ],
        [
            'name'        => 'randevu_durum_guncelle',
            'description' => 'Tek bir randevunun durumunu değiştirir: onayla, iptal et, tamamla vb. Onay gerektirir.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'randevu_id' => ['type' => 'integer', 'description' => 'Randevu ID (listede [#123] şeklinde gösterilir)'],
                    'yeni_durum' => ['type' => 'string', 'description' => 'onaylandi | tamamlandi | iptal | beklemede'],
                ],
                'required' => ['randevu_id', 'yeni_durum'],
            ],
        ],
        [
            'name'        => 'randevular_durum_toplu_guncelle',
            'description' => 'Birden fazla randevunun durumunu aynı anda günceller. "Hepsini onayla", "tümünü iptal et", "hepsi tamamlandı" gibi çoklu güncelleme taleplerinde kullan. Onay gerektirir.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'randevu_idler' => [
                        'type'        => 'array',
                        'items'       => ['type' => 'integer'],
                        'description' => 'Güncellenecek randevu ID listesi',
                    ],
                    'yeni_durum' => ['type' => 'string', 'description' => 'onaylandi | tamamlandi | iptal | beklemede'],
                ],
                'required' => ['randevu_idler', 'yeni_durum'],
            ],
        ],
        [
            'name'        => 'randevu_tasi',
            'description' => 'Mevcut bir randevuyu başka bir tarih ve/veya saate taşır. Onay gerektirir.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'randevu_id' => ['type' => 'integer', 'description' => 'Taşınacak randevunun ID\'si'],
                    'yeni_tarih' => ['type' => 'string', 'description' => 'Yeni tarih YYYY-MM-DD'],
                    'yeni_saat'  => ['type' => 'string', 'description' => 'Yeni saat HH:MM'],
                ],
                'required' => ['randevu_id', 'yeni_tarih', 'yeni_saat'],
            ],
        ],
        [
            'name'        => 'randevu_olustur',
            'description' => 'Yeni boş randevu bloğu oluşturur. Onay gerektirir.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'tarih'     => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                    'saat'      => ['type' => 'string', 'description' => 'HH:MM'],
                    'hizmet_id' => ['type' => 'integer', 'description' => 'Opsiyonel hizmet ID'],
                    'not'       => ['type' => 'string', 'description' => 'Opsiyonel not'],
                ],
                'required' => ['tarih', 'saat'],
            ],
        ],
        [
            'name'        => 'takvim_ac',
            'description' => 'Belirli bir tarihte mevcut takvim kapatma bloklarını kaldırır ve randevu alımını tekrar açar. "Takvimi aç", "açık olacağım", "iptal et kapatmayı", "randevu alabilsinler" gibi ifadeler için kullan. Onay gerektirir.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'tarih' => ['type' => 'string', 'description' => 'YYYY-MM-DD — bloğu kaldırılacak tarih'],
                ],
                'required' => ['tarih'],
            ],
        ],
        [
            'name'        => 'takvim_kapat',
            'description' => 'Belirtilen tarih/saat aralığında yeni randevu alımını kapatır (izin/blok). "Kapat", "randevu alma", "işim çıktı", "müsait değilim", "tatil", "izin" gibi ifadeler için kullan. Mevcut randevular sistem tarafından sorulur — önce randevu_listele çağırma.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'baslangic_zaman' => ['type' => 'string', 'description' => 'YYYY-MM-DD HH:MM'],
                    'bitis_zaman'     => ['type' => 'string', 'description' => 'YYYY-MM-DD HH:MM'],
                    'aciklama'        => ['type' => 'string', 'description' => 'Opsiyonel açıklama'],
                ],
                'required' => ['baslangic_zaman', 'bitis_zaman'],
            ],
        ],
        [
            'name'        => 'ozet_ver',
            'description' => 'Randevu istatistiklerini özetler (bugün, hafta, ay).',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'periyot' => ['type' => 'string', 'description' => 'bugun | hafta | ay'],
                ],
                'required' => [],
            ],
        ],
        [
            'name'        => 'bekleme_listesi_goster',
            'description' => 'Hekimin bekleme listesindeki hastaları listeler.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'durum' => ['type' => 'string', 'description' => 'Opsiyonel: beklemede | bildirildi | tamamlandi'],
                ],
                'required' => [],
            ],
        ],
        [
            'name'        => 'randevu_notu_guncelle',
            'description' => 'Bir randevunun notunu ekler veya günceller. Onay gerektirir.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'randevu_id' => ['type' => 'integer', 'description' => 'Randevu ID'],
                    'not'        => ['type' => 'string', 'description' => 'Eklenecek veya güncellenecek not metni'],
                ],
                'required' => ['randevu_id', 'not'],
            ],
        ],
        [
            'name'        => 'hizmetleri_listele',
            'description' => 'Hekimin tanımlı hizmetlerini (muayene türleri) listeler. Randevu oluştururken hizmet ID\'si gerekirse önce bunu çağır.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [],
                'required'   => [],
            ],
        ],
        [
            'name'        => 'hasta_randevulari',
            'description' => 'Hasta adıyla arama yaparak o hastanın randevularını listeler.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'arama_metni' => ['type' => 'string', 'description' => 'Hasta adı veya soyadı'],
                ],
                'required' => ['arama_metni'],
            ],
        ],
        [
            'name'        => 'profil_seo_incele',
            'description' => 'Hekimin profil bilgilerini (biyografi, uzmanlık, sosyal medya, görünürlük) SEO açısından analiz etmek için getirir. Eksik veya yetersiz alanlar için öneri sun.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [],
                'required'   => [],
            ],
        ],
        [
            'name'        => 'blog_seo_incele',
            'description' => 'Hekimin blog yazılarını meta başlık, meta açıklama, anahtar kelimeler ve içerik uzunluğu açısından analiz etmek için getirir. Tüm yazıları veya tek bir yazıyı inceleyebilir.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'blog_id' => ['type' => 'integer', 'description' => 'Opsiyonel: sadece bu ID\'li yazıyı incele'],
                ],
                'required' => [],
            ],
        ],
    ];

    private const YAZMA_FONKSIYONLARI = ['randevu_olustur', 'takvim_kapat', 'takvim_kapat_ve_iptal', 'takvim_ac', 'randevu_durum_guncelle', 'randevular_durum_toplu_guncelle', 'randevu_tasi', 'randevu_notu_guncelle'];

    private const KISAYOLLAR = [
        ['desen' => '/bekleyen randev|onay bekl/iu', 'fonksiyon' => 'randevu_listele', 'parametreler' => ['tarih_baslangic' => 'today', 'tarih_bitis' => '+30 days', 'durum' => 'beklemede']],
        ['desen' => '/(bugün|bugünkü).*(listele|göster|neler)|randevularımı listele/iu', 'fonksiyon' => 'randevu_listele', 'parametreler' => ['tarih_baslangic' => 'today', 'tarih_bitis' => 'today']],
        ['desen' => '/bu hafta.*(özet|kaç)|haftalık özet|haftanın randevu özeti/iu', 'fonksiyon' => 'ozet_ver', 'parametreler' => ['periyot' => 'hafta']],
        ['desen' => '/bu ay.*(özet|kaç)|aylık özet/iu', 'fonksiyon' => 'ozet_ver', 'parametreler' => ['periyot' => 'ay']],
        ['desen' => '/bugün.*(kaç|özet)|bugünkü özet/iu', 'fonksiyon' => 'ozet_ver', 'parametreler' => ['periyot' => 'bugun']],
    ];

    public function __construct(protected AsistanFonksiyonService $fonksiyonService) {}

    public function gunlukLimitAsildi(int $doktorId): bool
    {
        $anahtar = "asistan_limit_{$doktorId}_" . now()->format('Ymd');
        return (int) Cache::get($anahtar, 0) >= self::GUNLUK_LIMIT;
    }

    private function gunlukSayaciArtir(int $doktorId): void
    {
        $anahtar = "asistan_limit_{$doktorId}_" . now()->format('Ymd');
        Cache::add($anahtar, 0, now()->endOfDay());
        Cache::increment($anahtar);
    }

    public function isle(int $doktorId, string $mesaj, array $gecmis = []): array
    {
        // Shortcut patterns — bypass Gemini
        foreach (self::KISAYOLLAR as $kisayol) {
            if (preg_match($kisayol['desen'], $mesaj)) {
                $params = $kisayol['parametreler'];
                if (isset($params['tarih_baslangic']) && $params['tarih_baslangic'] === 'today') {
                    $params['tarih_baslangic'] = now()->toDateString();
                }
                if (isset($params['tarih_bitis']) && str_starts_with($params['tarih_bitis'], '+')) {
                    $params['tarih_bitis'] = now()->addDays(30)->toDateString();
                }
                $sonuc = $this->fonksiyonCalistir($kisayol['fonksiyon'], $doktorId, $params);
                return ['yanit' => $this->sonucuBicimlendir($kisayol['fonksiyon'], $sonuc), 'onay_gerekli' => null];
            }
        }

        if ($this->gunlukLimitAsildi($doktorId)) {
            return ['yanit' => 'Günlük AI kullanım limitinize (100 istek) ulaştınız. Yarın tekrar deneyebilirsiniz.', 'onay_gerekli' => null];
        }

        $apiKey = config('services.gemini.key');
        if (! $apiKey) {
            return ['yanit' => 'AI servisi yapılandırılmamış.', 'onay_gerekli' => null];
        }

        $model  = config('services.gemini.model', 'gemini-3.1-flash-lite');
        $today  = now()->locale('tr')->isoFormat('dddd, D MMMM YYYY');
        $system = <<<PROMPT
Sen Randevu Ajandam hekim paneli asistanısın. Bugün: {$today}.

KURALLAR:
1. Türkçe yanıt ver, kısa ve net ol.
2. Kullanıcı eylem belirttiğinde HEMEN fonksiyonu çağır.
   - Tek randevu güncelle (onayla/iptal et/tamamla) → randevu_durum_guncelle
   - Birden fazla randevu güncelle → randevular_durum_toplu_guncelle (tüm ID'leri listeye ekle)
   - "Hepsini onayla", "tümünü iptal et" → randevular_durum_toplu_guncelle
   - ÖNEMLI: Kullanıcı randevu ID belirtmeden durum güncellemek istiyorsa ÖNCE randevu_listele çağır (bugün için), kullanıcı listeden seçsin. ID varsa doğrudan güncelle.
3. TAKVİM KAPATMA: "takvimi kapat", "randevu alma", "müsait değilim", "tatilim var", "izin alıyorum" → takvim_kapat. Mevcut randevuların ne yapılacağını sistem sorar.
   NOT: "randevuyu iptal et / kapat" → takvim_kapat DEĞİL, randevu_durum_guncelle (iptal).
4. TAKVİM AÇMA: "takvimi aç", "geleceğim", "çalışıyorum" → takvim_ac.
5. Konuşma geçmişinde randevular [#ID] formatında listelenir. Kullanıcı saat, tarih veya sıra ile randevu belirtirse geçmişten eşleştir ve ID'yi bul — ASLA tekrar randevu_listele çağırma. Yalnızca geçmişte hiç liste yoksa listele.
6. Yazma işlemleri onay adımına yönlendirilir, sen sadece fonksiyonu çağır.
7. Hasta adı/telefon/TC bilgisi asla paylaşma.
8. Profil ve blog SEO analizinde: eksik/yetersiz alanları somut örneklerle göster, iyileştirme önerisi sun. Değiştirme yetkisi yok — sadece öneri yap.
PROMPT;

        // Build correct multi-turn contents: system first, then history, then current message
        $contents   = [];
        $contents[] = ['role' => 'user',  'parts' => [['text' => $system]]];
        $contents[] = ['role' => 'model', 'parts' => [['text' => 'Anlaşıldı, size yardımcı olmaya hazırım.']]];

        foreach (array_slice($gecmis, -10) as $h) {
            if (isset($h['rol'], $h['mesaj'])) {
                $contents[] = [
                    'role'  => $h['rol'] === 'asistan' ? 'model' : 'user',
                    'parts' => [['text' => $h['mesaj']]],
                ];
            }
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $this->zenginlestirMesaj($mesaj, $gecmis)]]];

        $url  = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Aç/kapat intent → Gemini'yi doğru fonksiyona zorla
        // acIntent: takvim bloğunu KALDIRMA niyeti (açmak, geri döndürmek)
        $acIntent = (bool) preg_match(
            '/takvim[i]?\s*aç|aç\s*takvim|takvim[i]?\s*kaldır|randevu\s*aç|açık\s*ol(?:sun|acak|sın)?|çalışıyorum|çalışacağım|bloğu?\s*kaldır|kapatmay[ıi]\s*iptal|geleceğim\b|müsait\s*olacağ/iu',
            $mesaj
        );
        // kapatIntent: takvim KAPATMA niyeti — "kapat" ancak "takvim" bağlamında tetikler;
        // "randevuyu kapat/iptal" → takvim değil randevu işlemi, bu yüzden dahil değil
        $kapatIntent = ! $acIntent && (bool) preg_match(
            '/takvim[i]?\s*kapat|kapat\s*takvim|randevu\s*alma[ym]?|randevu\s*durdur|müsait\s*değil|işim\s*çıktı|tatilim?\s*var|tatile\s*çık|izin\s*al|iznim\s*var|çalışmıyorum|gelmeyeceğim/iu',
            $mesaj
        );

        $toolConfig = match (true) {
            $acIntent    => ['function_calling_config' => ['mode' => 'ANY', 'allowed_function_names' => ['takvim_ac']]],
            $kapatIntent => ['function_calling_config' => ['mode' => 'ANY', 'allowed_function_names' => ['takvim_kapat']]],
            default      => ['function_calling_config' => ['mode' => 'AUTO']],
        };

        $body = [
            'contents'    => $contents,
            'tools'       => [['function_declarations' => $this->geminiFonksiyonTanimlari()]],
            'tool_config' => $toolConfig,
        ];

        try {
            $resp = $this->geminiIstek($url, $body, 20);
        } catch (\Throwable $e) {
            Log::error('Asistan Gemini timeout', ['error' => $e->getMessage()]);
            return ['yanit' => 'Şu an AI servisine ulaşılamıyor. Lütfen tekrar deneyin.', 'onay_gerekli' => null];
        }

        if (! $resp->successful()) {
            $status = $resp->status();
            if ($status === 429) {
                $data  = $resp->json();
                $delay = $data['error']['details'][2]['retryDelay'] ?? null;
                $sure  = $delay ? (' ' . preg_replace('/[^0-9]/', '', (string) $delay) . ' saniye sonra tekrar deneyin.') : '';
                return ['yanit' => 'AI servis kotası doldu.' . $sure, 'onay_gerekli' => null];
            }
            Log::error('Asistan Gemini hata', ['status' => $status, 'body' => $resp->body()]);
            return ['yanit' => 'AI servisinde geçici bir sorun oluştu.', 'onay_gerekli' => null];
        }

        $this->gunlukSayaciArtir($doktorId);

        $parsed = $this->geminiParcaAyikla($resp->json());
        if ($parsed['functionCall']) {
            $fonksiyonAdi = $parsed['functionCall']['name'] ?? '';
            $parametreler = (array) ($parsed['functionCall']['args'] ?? []);

            if (in_array($fonksiyonAdi, self::YAZMA_FONKSIYONLARI, true)) {
                return $this->yazmaFonksiyonuIsle($fonksiyonAdi, $parametreler, $doktorId);
            }

            $sonuc  = $this->fonksiyonCalistir($fonksiyonAdi, $doktorId, $parametreler);
            $adimIki = $this->sonucuGeminiyeGonder(
                $url,
                $body,
                $parsed['functionCallPart'] ?? ['functionCall' => $parsed['functionCall']],
                $fonksiyonAdi,
                $sonuc,
                $doktorId
            );

            if (is_array($adimIki)) {
                return $adimIki;
            }
            return ['yanit' => $adimIki, 'onay_gerekli' => null];
        }

        if ($parsed['text'] !== '') {
            return ['yanit' => $parsed['text'], 'onay_gerekli' => null];
        }

        return ['yanit' => 'Anlamadım, farklı bir şekilde sorar mısınız?', 'onay_gerekli' => null];
    }

    /**
     * PHP boş array'i JSON [] yapar; Gemini parameters.properties için {} (map) ister.
     * Argümansız fonksiyonlarda parameters alanını hiç göndermemek en güvenlisi.
     */
    private function geminiFonksiyonTanimlari(): array
    {
        $defs = self::FONKSIYON_TANIMLARI;
        foreach ($defs as $i => $def) {
            if (! isset($def['parameters']) || ! is_array($def['parameters'])) {
                continue;
            }
            if (($def['parameters']['required'] ?? null) === []) {
                unset($defs[$i]['parameters']['required']);
            }
            if (($defs[$i]['parameters']['properties'] ?? null) === []) {
                unset($defs[$i]['parameters']);
            }
        }

        return $defs;
    }

    /**
     * Guzzle json seçeneği iç içe stdClass'ı bazen []'e çevirir.
     * Gövdeyi kendimiz encode edip ham JSON gönderiyoruz.
     */
    private function geminiIstek(string $url, array $body, int $timeout)
    {
        $json = json_encode($this->geminiJsonNormalize($body), JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Gemini isteği JSON kodlanamadı.');
        }

        return Http::timeout($timeout)
            ->withBody($json, 'application/json')
            ->post($url);
    }

    private function geminiJsonNormalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $k => $v) {
            if ($k === 'properties' && $v === []) {
                $value[$k] = new \stdClass();
                continue;
            }
            if ($k === 'required' && $v === []) {
                unset($value[$k]);
                continue;
            }
            $value[$k] = $this->geminiJsonNormalize($v);
        }

        return $value;
    }

    /**
     * Gemini bazen thought + functionCall'u ayrı parts'ta döner; yalnızca parts[0] okunursa çağrı kaybolur.
     *
     * @return array{text: string, functionCall: ?array, functionCallPart: ?array}
     */
    private function geminiParcaAyikla(?array $payload): array
    {
        $parts = $payload['candidates'][0]['content']['parts'] ?? [];
        $text = '';
        $functionCall = null;
        $functionCallPart = null;

        foreach (is_array($parts) ? $parts : [] as $part) {
            if (! is_array($part)) {
                continue;
            }
            $call = $part['functionCall'] ?? $part['function_call'] ?? null;
            if (is_array($call) && ! empty($call['name'])) {
                $functionCall = $call;
                $functionCallPart = isset($part['functionCall'])
                    ? ['functionCall' => $call]
                    : ['functionCall' => $call];
            }
            if (isset($part['text']) && is_string($part['text']) && $part['text'] !== '') {
                $text .= $part['text'];
            }
        }

        return [
            'text' => trim($text),
            'functionCall' => $functionCall,
            'functionCallPart' => $functionCallPart,
        ];
    }

    public function onayliIsleCalistir(int $doktorId, string $fonksiyon, array $parametreler): array
    {
        if (! in_array($fonksiyon, self::YAZMA_FONKSIYONLARI, true)) {
            return ['yanit' => 'Geçersiz işlem.'];
        }

        $sonuc = $this->fonksiyonCalistir($fonksiyon, $doktorId, $parametreler);

        if (isset($sonuc['basari']) && ! $sonuc['basari']) {
            return ['yanit' => 'Hata: ' . ($sonuc['hata'] ?? 'İşlem başarısız.')];
        }

        return ['yanit' => $this->sonucuBicimlendir($fonksiyon, $sonuc)];
    }

    private function fonksiyonCalistir(string $ad, int $doktorId, array $params): array
    {
        return match ($ad) {
            'randevu_listele' => $this->fonksiyonService->randevu_listele(
                $doktorId,
                $params['tarih_baslangic'] ?? now()->toDateString(),
                $params['tarih_bitis']     ?? now()->toDateString(),
                $params['durum']           ?? null,
            ),
            'bos_saat_bul' => $this->fonksiyonService->bos_saat_bul(
                $doktorId,
                $params['tarih'] ?? now()->toDateString(),
            ),
            'randevu_durum_guncelle' => $this->fonksiyonService->randevu_durum_guncelle(
                $doktorId,
                (int) ($params['randevu_id'] ?? 0),
                $params['yeni_durum'] ?? 'onaylandi',
            ),
            'randevular_durum_toplu_guncelle' => $this->fonksiyonService->randevular_durum_toplu_guncelle(
                $doktorId,
                array_map('intval', (array) ($params['randevu_idler'] ?? [])),
                $params['yeni_durum'] ?? 'onaylandi',
            ),
            'randevu_tasi' => $this->fonksiyonService->randevu_tasi(
                $doktorId,
                (int) ($params['randevu_id'] ?? 0),
                $params['yeni_tarih'] ?? now()->toDateString(),
                $params['yeni_saat']  ?? '09:00',
            ),
            'randevu_olustur' => $this->fonksiyonService->randevu_olustur(
                $doktorId,
                $params['tarih']     ?? now()->toDateString(),
                $params['saat']      ?? '09:00',
                isset($params['hizmet_id']) ? (int) $params['hizmet_id'] : null,
                $params['not']       ?? '',
            ),
            'takvim_ac' => $this->fonksiyonService->takvim_ac(
                $doktorId,
                $params['tarih'] ?? now()->toDateString(),
            ),
            'takvim_kapat' => $this->fonksiyonService->takvim_kapat(
                $doktorId,
                $params['baslangic_zaman'] ?? now()->toDateTimeString(),
                $params['bitis_zaman']     ?? now()->addHour()->toDateTimeString(),
                $params['aciklama']        ?? '',
            ),
            'takvim_kapat_ve_iptal' => $this->fonksiyonService->takvim_kapat_ve_iptal(
                $doktorId,
                $params['baslangic_zaman'] ?? now()->toDateTimeString(),
                $params['bitis_zaman']     ?? now()->addHour()->toDateTimeString(),
                $params['aciklama']        ?? '',
                $params['_secim']          ?? 'kapat_ve_iptal',
            ),
            'ozet_ver' => $this->fonksiyonService->ozet_ver(
                $doktorId,
                $params['periyot'] ?? 'bugun',
            ),
            'bekleme_listesi_goster' => $this->fonksiyonService->bekleme_listesi_goster(
                $doktorId,
                $params['durum'] ?? null,
            ),
            'randevu_notu_guncelle' => $this->fonksiyonService->randevu_notu_guncelle(
                $doktorId,
                (int) ($params['randevu_id'] ?? 0),
                $params['not'] ?? '',
            ),
            'hizmetleri_listele' => $this->fonksiyonService->hizmetleri_listele($doktorId),
            'hasta_randevulari'  => $this->fonksiyonService->hasta_randevulari(
                $doktorId,
                $params['arama_metni'] ?? '',
            ),
            'profil_seo_incele' => $this->fonksiyonService->profil_seo_incele($doktorId),
            'blog_seo_incele'   => $this->fonksiyonService->blog_seo_incele(
                $doktorId,
                isset($params['blog_id']) ? (int) $params['blog_id'] : null,
            ),
            default => ['hata' => 'Bilinmeyen fonksiyon'],
        };
    }

    private function yazmaFonksiyonuIsle(string $fonksiyonAdi, array $parametreler, int $doktorId): array
    {
        if ($fonksiyonAdi === 'takvim_kapat') {
            $kontrol = $this->fonksiyonService->takvim_cakisma_kontrol(
                $doktorId,
                $parametreler['baslangic_zaman'] ?? now()->toDateTimeString(),
                $parametreler['bitis_zaman']     ?? now()->addHour()->toDateTimeString(),
            );
            if ($kontrol['etkilenen_sayi'] > 0) {
                $liste = implode("\n", array_map(
                    fn ($r) => "• [#{$r['id']}] {$r['tarih']} {$r['gun']} {$r['saat']} — {$r['hizmet']} ({$r['durum']})",
                    $kontrol['randevular']
                ));
                return [
                    'yanit'         => "Bu aralıkta {$kontrol['etkilenen_sayi']} aktif randevunuz var:\n{$liste}",
                    'secim_gerekli' => [
                        'baslik'       => 'Bu randevularla ne yapalım?',
                        'parametreler' => $parametreler,
                        'secenekler'   => $this->takvimKapatSecenek($doktorId),
                    ],
                ];
            }
        }

        return [
            'yanit'        => $this->onayMetniOlustur($fonksiyonAdi, $parametreler),
            'onay_gerekli' => ['fonksiyon' => $fonksiyonAdi, 'parametreler' => $parametreler],
        ];
    }

    private function sonucuGeminiyeGonder(string $url, array $origBody, array $functionCallPart, string $fonksiyonAdi, array $sonuc, int $doktorId): string|array
    {
        $contents   = $origBody['contents'];
        $contents[] = ['role' => 'model', 'parts' => [$functionCallPart]];
        $contents[] = [
            'role'  => 'user',
            'parts' => [[
                'functionResponse' => [
                    'name'     => $fonksiyonAdi,
                    'response' => ['result' => $sonuc],
                ],
            ]],
        ];

        try {
            $resp2 = $this->geminiIstek($url, array_merge($origBody, ['contents' => $contents]), 15);
            if (! $resp2->successful()) {
                return $this->sonucuBicimlendir($fonksiyonAdi, $sonuc);
            }

            $parsed2 = $this->geminiParcaAyikla($resp2->json());
            if ($parsed2['functionCall']) {
                $fonksiyon2 = $parsed2['functionCall']['name'] ?? '';
                $parametreler2 = (array) ($parsed2['functionCall']['args'] ?? []);

                if (in_array($fonksiyon2, self::YAZMA_FONKSIYONLARI, true)) {
                    return $this->yazmaFonksiyonuIsle($fonksiyon2, $parametreler2, $doktorId);
                }
                $sonuc2 = $this->fonksiyonCalistir($fonksiyon2, $doktorId, $parametreler2);
                return $this->sonucuBicimlendir($fonksiyon2, $sonuc2);
            }

            if ($parsed2['text'] !== '') {
                return $parsed2['text'];
            }
        } catch (\Throwable) {
        }

        return $this->sonucuBicimlendir($fonksiyonAdi, $sonuc);
    }

    private function sonucuBicimlendir(string $fonksiyon, array $sonuc): string
    {
        return match ($fonksiyon) {
            'ozet_ver' => sprintf(
                '%s özeti: Toplam %d randevu — %d beklemede, %d onaylı, %d tamamlandı, %d iptal.',
                $sonuc['periyot']    ?? 'Dönem',
                $sonuc['toplam']     ?? 0,
                $sonuc['beklemede']  ?? 0,
                $sonuc['onaylandi']  ?? 0,
                $sonuc['tamamlandi'] ?? 0,
                $sonuc['iptal']      ?? 0,
            ),
            'randevu_listele'        => $this->randevuListesiMetni($sonuc),
            'bos_saat_bul'           => $this->bosSlotMetni($sonuc),
            'randevu_durum_guncelle' => ($sonuc['basari'] ?? false)
                ? "Güncellendi: Randevu #{$sonuc['randevu_id']} — {$sonuc['eski_durum']} → {$sonuc['yeni_durum']} ({$sonuc['tarih']} {$sonuc['saat']})."
                : 'Hata: ' . ($sonuc['hata'] ?? 'Bilinmeyen hata'),
            'randevular_durum_toplu_guncelle' => ($sonuc['basari'] ?? false)
                ? (function () use ($sonuc): string {
                    $durumEtiket = match ($sonuc['yeni_durum'] ?? '') {
                        'onaylandi'  => 'onaylandı',
                        'tamamlandi' => 'tamamlandı olarak işaretlendi',
                        'iptal'      => 'iptal edildi',
                        'beklemede'  => 'beklemeye alındı',
                        default      => $sonuc['yeni_durum'],
                    };
                    $msg = "{$sonuc['guncellenen']} randevu {$durumEtiket}.";
                    if (! empty($sonuc['hata_idler'])) {
                        $msg .= ' Bulunamayan ID: #' . implode(', #', $sonuc['hata_idler']) . '.';
                    }
                    return $msg;
                })()
                : 'Hata: ' . ($sonuc['hata'] ?? 'Bilinmeyen hata'),
            'randevu_tasi' => ($sonuc['basari'] ?? false)
                ? "Taşındı: Randevu #{$sonuc['randevu_id']} → {$sonuc['yeni_tarih']} {$sonuc['yeni_saat']}."
                : 'Hata: ' . ($sonuc['hata'] ?? 'Bilinmeyen hata'),
            'randevu_olustur' => ($sonuc['basari'] ?? false)
                ? "Randevu oluşturuldu: {$sonuc['tarih']} {$sonuc['saat']}. {$sonuc['mesaj']}"
                : 'Hata: ' . ($sonuc['hata'] ?? 'Bilinmeyen hata'),
            'takvim_ac' => ($sonuc['basari'] ?? false)
                ? $sonuc['mesaj']
                : 'Hata: ' . ($sonuc['hata'] ?? 'Bilinmeyen hata'),
            'takvim_kapat' => ($sonuc['basari'] ?? false)
                ? "Takvim kapatıldı: {$sonuc['baslangic']} – {$sonuc['bitis']}."
                : 'Hata: ' . ($sonuc['hata'] ?? 'Bilinmeyen hata'),
            'takvim_kapat_ve_iptal' => ($sonuc['basari'] ?? false)
                ? (function () use ($sonuc): string {
                    $msg = "{$sonuc['iptal_edilen']} randevu iptal edildi.";
                    if (($sonuc['secim'] ?? '') === 'kapat_iptal_sms') {
                        $msg .= ' Hastalara SMS bildirimi gönderildi.';
                    }
                    if (($sonuc['bekleme_eklenen'] ?? 0) > 0) {
                        $msg .= " {$sonuc['bekleme_eklenen']} hasta bekleme listesine eklendi.";
                    }
                    return $msg . " Takvim kapatıldı: {$sonuc['baslangic']} – {$sonuc['bitis']}.";
                })()
                : 'Hata: ' . ($sonuc['hata'] ?? 'Bilinmeyen hata'),
            'bekleme_listesi_goster' => (function () use ($sonuc): string {
                if (($sonuc['toplam'] ?? 0) === 0) {
                    return 'Bekleme listesi boş.';
                }
                $satirlar = array_map(
                    fn ($b) => "• [#{$b['id']}] {$b['ad']} — {$b['hizmet']} — {$b['tercih_tarih']} ({$b['durum']})",
                    $sonuc['liste']
                );
                return "Bekleme listesinde {$sonuc['toplam']} kişi:\n" . implode("\n", $satirlar);
            })(),
            'randevu_notu_guncelle' => ($sonuc['basari'] ?? false)
                ? "Randevu #{$sonuc['randevu_id']} notu güncellendi ({$sonuc['tarih']} {$sonuc['saat']})."
                : 'Hata: ' . ($sonuc['hata'] ?? 'Bilinmeyen hata'),
            'hizmetleri_listele' => (function () use ($sonuc): string {
                if (($sonuc['toplam'] ?? 0) === 0) {
                    return 'Tanımlı hizmet bulunamadı.';
                }
                $satirlar = array_map(function ($h) {
                    $detay = array_filter([$h['sure'], $h['fiyat']]);
                    return "• [#{$h['id']}] {$h['ad']}" . (! empty($detay) ? ' — ' . implode(' / ', $detay) : '');
                }, $sonuc['hizmetler']);
                return "Hizmetleriniz ({$sonuc['toplam']} adet):\n" . implode("\n", $satirlar);
            })(),
            'hasta_randevulari' => (function () use ($sonuc): string {
                if (($sonuc['toplam'] ?? 0) === 0) {
                    return '«' . $sonuc['arama'] . '» için randevu bulunamadı.';
                }
                $satirlar = array_map(
                    fn ($r) => "• [#{$r['id']}] {$r['tarih']} {$r['gun']} {$r['saat']} — {$r['hizmet']} ({$r['durum']})",
                    $sonuc['randevular']
                );
                return "«{$sonuc['arama']}» için {$sonuc['toplam']} randevu:\n" . implode("\n", $satirlar);
            })(),
            'profil_seo_incele' => 'Profil bilgileriniz alındı. Analiz yapılıyor...',
            'blog_seo_incele'   => ($sonuc['toplam'] ?? 0) === 0
                ? 'Blog yazısı bulunamadı.'
                : ($sonuc['toplam'] . ' blog yazısı alındı. Analiz yapılıyor...'),
            default => json_encode($sonuc, JSON_UNESCAPED_UNICODE),
        };
    }

    private function randevuListesiMetni(array $sonuc): string
    {
        if (empty($sonuc['randevular'])) {
            return 'Belirtilen dönemde randevu bulunamadı.';
        }

        $satirlar = array_map(
            fn ($r) => "• [#{$r['id']}] {$r['tarih']} {$r['gun']} {$r['saat']} — {$r['hizmet']} ({$r['durum']})",
            $sonuc['randevular']
        );

        return "Toplam {$sonuc['toplam']} randevu:\n" . implode("\n", $satirlar);
    }

    private function bosSlotMetni(array $sonuc): string
    {
        if (($sonuc['bos_slot_sayisi'] ?? 0) === 0) {
            return "{$sonuc['tarih']} {$sonuc['gun']} tarihinde müsait slot yok.";
        }

        $saatler = implode(', ', array_slice($sonuc['bos_saatler'], 0, 10));
        $fazla   = count($sonuc['bos_saatler']) > 10 ? ' ve daha fazlası' : '';
        return "{$sonuc['tarih']} {$sonuc['gun']} — {$sonuc['bos_slot_sayisi']} boş slot: {$saatler}{$fazla}.";
    }

    private function onayMetniOlustur(string $fonksiyon, array $params): string
    {
        return match ($fonksiyon) {
            'randevu_durum_guncelle' => (function () use ($params) {
                $durumEtiket = match ($params['yeni_durum'] ?? '') {
                    'onaylandi'  => 'onaylanacak',
                    'tamamlandi' => 'tamamlandı olarak işaretlenecek',
                    'iptal'      => 'iptal edilecek',
                    'beklemede'  => 'beklemeye alınacak',
                    default      => $params['yeni_durum'] ?? '?',
                };
                return "Randevu #{$params['randevu_id']} {$durumEtiket}. Onaylıyor musunuz?";
            })(),
            'randevular_durum_toplu_guncelle' => (function () use ($params) {
                $idler = array_map('intval', (array) ($params['randevu_idler'] ?? []));
                $sayi  = count($idler);
                $durumEtiket = match ($params['yeni_durum'] ?? '') {
                    'onaylandi'  => 'onaylanacak',
                    'tamamlandi' => 'tamamlandı olarak işaretlenecek',
                    'iptal'      => 'iptal edilecek',
                    'beklemede'  => 'beklemeye alınacak',
                    default      => $params['yeni_durum'] ?? '?',
                };
                $idListesi = implode(', #', $idler);
                return "{$sayi} randevu {$durumEtiket} (#" . $idListesi . "). Onaylıyor musunuz?";
            })(),
            'randevu_tasi' => (function () use ($params) {
                $yeniTarih = isset($params['yeni_tarih']) ? Carbon::parse($params['yeni_tarih'])->format('d.m.Y') : '?';
                $yeniSaat  = $params['yeni_saat'] ?? '?';
                return "Randevu #{$params['randevu_id']} → {$yeniTarih} {$yeniSaat} tarihine taşınacak. Onaylıyor musunuz?";
            })(),
            'randevu_olustur' => (function () use ($params) {
                $tarih = isset($params['tarih']) ? Carbon::parse($params['tarih'])->format('d.m.Y') : '?';
                return "{$tarih} {$params['saat']} için yeni randevu bloğu oluşturulacak. Onaylıyor musunuz?";
            })(),
            'takvim_ac' => (function () use ($params): string {
                $tarih = isset($params['tarih']) ? Carbon::parse($params['tarih'])->format('d.m.Y') : '?';
                return "{$tarih} tarihindeki takvim bloğu kaldırılacak, randevu alımı tekrar açılacak. Onaylıyor musunuz?";
            })(),
            'takvim_kapat' => (function () use ($params) {
                $bas = isset($params['baslangic_zaman']) ? Carbon::parse($params['baslangic_zaman'])->format('d.m.Y H:i') : '?';
                $bit = isset($params['bitis_zaman'])     ? Carbon::parse($params['bitis_zaman'])->format('d.m.Y H:i')     : '?';
                return "{$bas} – {$bit} arası takvimde kapatılacak. Onaylıyor musunuz?";
            })(),
            'randevu_notu_guncelle' => (function () use ($params): string {
                $not = mb_substr($params['not'] ?? '', 0, 60);
                return "Randevu #{$params['randevu_id']} için not eklenecek: «{$not}». Onaylıyor musunuz?";
            })(),
            default => 'Bu işlemi onaylıyor musunuz?',
        };
    }

    private function zenginlestirMesaj(string $mesaj, array $gecmis): string
    {
        // Geçmiş asistan mesajlarından saat → randevu_id haritası çıkar
        $idHaritasi = [];
        foreach (array_reverse(array_slice($gecmis, -10)) as $h) {
            if (($h['rol'] ?? '') !== 'asistan' || empty($h['mesaj'])) {
                continue;
            }
            // Format: • [#72] 06.08.2026 Perşembe 16:10 — ...
            preg_match_all('/\[#(\d+)\][^\n]*?(\d{2}:\d{2})/', $h['mesaj'], $m, PREG_SET_ORDER);
            foreach ($m as $hit) {
                $idHaritasi[$hit[2]] = (int) $hit[1];
            }
            if (! empty($idHaritasi)) {
                break; // En güncel listeyi al
            }
        }

        if (empty($idHaritasi)) {
            return $mesaj;
        }

        // Hedef saat ifadelerini bul ve geçici olarak koru (zenginleştirme dışı bırak)
        // Örn: "13:40'a taşı", "14:00'e al", "09:30 saatine" → bunlar kaynak değil hedef
        $hedefDeseni = '/\b\d{1,2}[.:]\d{2}\b(?=\s*[\'aeıioöuü]*\s*(?:taşı|taşın|al(?:ın)?|götür|koy|yerleştir|saatine|için\s*yeni|için\s*randevu))/ui';
        $koruma = [];
        $korunanMesaj = preg_replace_callback($hedefDeseni, function ($m) use (&$koruma) {
            $yer = '__HEDEF_' . count($koruma) . '__';
            $koruma[$yer] = $m[0];
            return $yer;
        }, $mesaj);

        // Kalan saatlere ID enjekte et (kaynak saatler)
        $zengin = preg_replace_callback(
            '/\b(\d{1,2})[.:h](\d{2})\b/',
            function ($hit) use ($idHaritasi) {
                $saat = sprintf('%02d:%02d', (int) $hit[1], (int) $hit[2]);
                if (isset($idHaritasi[$saat])) {
                    return $hit[0] . ' [#' . $idHaritasi[$saat] . ']';
                }
                return $hit[0];
            },
            $korunanMesaj
        );

        // Korunan hedef saatlerini geri yerleştir
        return str_replace(array_keys($koruma), array_values($koruma), $zengin);
    }

    private function takvimKapatSecenek(int $doktorId): array
    {
        $doktor      = Doktor::find($doktorId);
        $smsAktif    = $doktor && PaketYetki::has($doktor, 'sms_hatirlatma') && config('sms.driver', 'log') !== 'log';
        $beklemeVar  = $doktor && PaketYetki::has($doktor, 'bekleme_listesi');

        $secenekler = [];

        $secenekler[] = [
            'deger' => 'sadece_kapat',
            'etiket' => 'Sadece takvimi kapat',
            'bilgi'  => 'Mevcut randevular olduğu gibi kalır, yeni randevu alımı durur.',
        ];

        if ($smsAktif) {
            $secenekler[] = [
                'deger'  => 'kapat_iptal_sms',
                'etiket' => 'Randevuları iptal et + SMS gönder',
                'bilgi'  => 'Tüm aktif randevular iptal edilir, hastalara otomatik iptal SMS\'i gönderilir.',
            ];
        }

        $secenekler[] = [
            'deger'  => 'kapat_ve_iptal',
            'etiket' => 'Randevuları iptal et ve kapat',
            'bilgi'  => 'Tüm aktif randevular iptal edilir. Hasta bildirim gönderilmez.',
        ];

        if ($beklemeVar) {
            $secenekler[] = [
                'deger'  => 'kapat_bekleme',
                'etiket' => 'İptal et + Bekleme listesine al',
                'bilgi'  => 'Randevular iptal edilir, hastalar bekleme listesine eklenir. Slot açıldığında sırayla bildirilirler.',
            ];
        }

        $secenekler[] = [
            'deger'  => 'vazgec',
            'etiket' => 'Vazgeç',
            'bilgi'  => '',
        ];

        return $secenekler;
    }
}
