<?php

namespace App\Support;

use App\Models\Doktor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paket özellik kontrolleri — web + mobil ortak.
 */
class PaketYetki
{
    /** @var array<string, string> */
    public static array $labels = [
        'randevu_talebi_goruntule' => 'Randevu taleplerini görme',
        'randevu_talepleri' => 'Randevu taleplerini yönetme',
        'online_takvim' => 'Online randevu takvimi',
        'bekleme_listesi' => 'Bekleme listesi',
        'hizli_slot' => 'Hızlı slot kapatma',
        'seri_randevu' => 'Seri randevu',
        'ical_export' => 'Takvim dışa aktarma',
        'email_bildirim' => 'E-posta bildirimi',
        'sms_hatirlatma' => 'SMS hatırlatma',
        'sms_baslik' => 'SMS başlığı',
        'no_show_mesaj' => 'No-show mesajı',
        'hasta_kartlari' => 'Hasta kartları',
        'hasta_not_dosya' => 'Hasta not / dosya',
        'tedavi_gecmisi' => 'Tedavi geçmişi',
        'onam_formu' => 'Onam formu',
        'hasta_export' => 'Hasta Excel aktarma',
        'profil_sayfasi' => 'Hekim profili',
        'dogrulanmis_rozet' => 'Doğrulanmış rozet',
        'iletisim_profilde' => 'Profil iletişim bilgisi',
        'hakkimda' => 'Hakkımda / özgeçmiş',
        'galeri' => 'Fotoğraf galerisi',
        'dis_baglanti' => 'Dış bağlantılar',
        'oncelikli_liste' => 'Öne çıkarma',
        'yorum_gorunur' => 'Yorum görünürlüğü',
        'yorum_yanit' => 'Yorum yanıtlama',
        'yorum_davet' => 'Yorum daveti',
        'finans' => 'Finans yönetimi',
        'hasta_bakiyeleri' => 'Hasta bakiyeleri',
        'finans_rapor' => 'Finans raporu',
        'blog' => 'Blog / makale',
        'faq' => 'S.S.S. yönetimi',
        'egitimler' => 'Eğitimler ve başvuru formu',
        'online_gorusme' => 'Online görüntülü görüşme',
        'web_sitesi' => 'Kişisel web sitesi',
        'klinik_web_sitesi' => 'Klinik web sitesi',
        'destek_email' => 'E-posta destek',
        'destek_oncelikli' => 'Öncelikli destek',
        'veri_tasima' => 'Veri taşıma',
    ];

    public static function label(string $code): string
    {
        return self::$labels[$code] ?? $code;
    }

    /**
     * @param  string|list<string>  $codes  OR mantığı
     */
    public static function has(Doktor $doktor, string|array $codes): bool
    {
        $list = is_array($codes) ? $codes : [$codes];
        $paket = $doktor->aktifPaket();

        return $paket ? $paket->hasAnyFeature($list) : false;
    }

    /**
     * Özellik yoksa Response döner; varsa null.
     *
     * @param  string|list<string>  $codes
     */
    public static function denyIfMissing(Request $request, Doktor $doktor, string|array $codes, ?string $message = null): ?Response
    {
        if (self::has($doktor, $codes)) {
            return null;
        }

        $list = is_array($codes) ? $codes : [$codes];
        $label = collect($list)->map(fn ($c) => self::label($c))->implode(' / ');
        $msg = $message ?: "«{$label}» mevcut paketinizde yer almıyor. Paketinizi yükselterek açabilirsiniz.";

        return self::deny($request, $msg, $list[0] ?? 'paket');
    }

    public static function deny(Request $request, string $message, string $feature = 'paket'): Response
    {
        $upgrade = route('frontend.hekim.paket_sec', ['degistir' => 1]);

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'upgrade_url' => $upgrade,
                'feature' => $feature,
            ], 403);
        }

        return redirect()
            ->route('frontend.hekim.paket_sec', ['degistir' => 1])
            ->with('hata', $message);
    }
}
