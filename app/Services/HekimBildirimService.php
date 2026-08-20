<?php

namespace App\Services;

use App\Models\Doktor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Hekim için canlı (persistent olmayan) bildirim üreticisi.
 *
 * Her bir bildirim aşağıdaki alanları içerir:
 *   - id      : Kaynak + kimlik birleşimi (dedup için)
 *   - tip     : randevu_talep | vadesi_gecen | randevu_iptal | yorum_bekliyor | randevu_bugun
 *   - onem    : kritik | uyari | bilgi
 *   - baslik  : Kısa başlık
 *   - mesaj   : Kısa açıklama (bir cümle)
 *   - url     : Tıklanınca gidilecek route URL'i
 *   - tarih   : Carbon — sıralama için (ne kadar yeni)
 *   - ikon    : SVG path (heroicons)
 *   - renk    : bg/text için renk anahtarı (rose|amber|blue|emerald|indigo|orange)
 */
class HekimBildirimService
{
    /**
     * Bir hekimin tüm aktif bildirimlerini getir.
     * Sıralama: önem (kritik → uyari → bilgi) sonra tarih (yeniden eskiye)
     */
    public function getBildirimler(Doktor $doktor): Collection
    {
        $bildirimler = collect()
            ->concat($this->onayBekleyenRandevular($doktor))
            ->concat($this->vadesiGecenFaturalar($doktor))
            ->concat($this->bugunIptalEdilenler($doktor))
            ->concat($this->onayBekleyenYorumlar($doktor))
            ->concat($this->bugunkuRandevular($doktor));

        $onemSirasi = ['kritik' => 0, 'uyari' => 1, 'bilgi' => 2];

        return $bildirimler->sortBy([
            fn ($a, $b) => ($onemSirasi[$a['onem']] ?? 9) <=> ($onemSirasi[$b['onem']] ?? 9),
            fn ($a, $b) => $b['tarih']->timestamp <=> $a['tarih']->timestamp,
        ])->values();
    }

    public function sayi(Doktor $doktor): int
    {
        return $this->getBildirimler($doktor)->count();
    }

    /* -------------------------------------------------------------------------
     |  KAYNAKLAR
     |-------------------------------------------------------------------------*/

    /**
     * Onay bekleyen randevu talepleri (gelecekteki tarihli).
     */
    private function onayBekleyenRandevular(Doktor $doktor): Collection
    {
        return $doktor->randevular()
            ->where('durum', 'beklemede')
            ->whereDate('tarih', '>=', Carbon::today())
            ->orderBy('tarih')
            ->take(20)
            ->get()
            ->map(function ($r) {
                $adSoyad = trim("{$r->ad} {$r->soyad}") ?: 'İsim yok';
                return [
                    'id'     => "randevu_talep_{$r->id}",
                    'tip'    => 'randevu_talep',
                    'onem'   => 'kritik',
                    'baslik' => 'Yeni randevu talebi',
                    'mesaj'  => "{$adSoyad} — " . Carbon::parse($r->tarih)->format('d.m.Y') . ' ' . substr((string) $r->saat, 0, 5),
                    'url'    => route('hekim.randevu.talepler'),
                    'tarih'  => $r->created_at ?? Carbon::now(),
                    'renk'   => 'amber',
                    'ikon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>',
                ];
            });
    }

    /**
     * 30+ gündür ödenmemiş faturalar (tek toplu bildirim).
     */
    private function vadesiGecenFaturalar(Doktor $doktor): Collection
    {
        $otuzGunOnce = Carbon::now()->subDays(30);

        $ozet = $doktor->odemeler()
            ->whereIn('durum', ['beklemede', 'kismi_odeme'])
            ->whereDate('odeme_tarihi', '<', $otuzGunOnce)
            ->selectRaw('COUNT(*) as adet, COALESCE(SUM(tutar - odenen_tutar), 0) as tutar, MAX(odeme_tarihi) as son_tarih')
            ->first();

        $adet = (int) ($ozet->adet ?? 0);
        if ($adet === 0) {
            return collect();
        }

        $tutar = (float) ($ozet->tutar ?? 0);

        return collect([[
            'id'     => 'vadesi_gecen_ozet',
            'tip'    => 'vadesi_gecen',
            'onem'   => 'kritik',
            'baslik' => "{$adet} tahsilat 30 günden uzun süredir açık",
            'mesaj'  => 'Bekleyen tutar: ' . number_format($tutar, 2, ',', '.') . ' ₺ — hasta cari hesaplarını gözden geçirin.',
            'url'    => route('hekim.finans.hasta-bakiyeleri', ['durum' => 'borclu']),
            'tarih'  => Carbon::parse($ozet->son_tarih ?? Carbon::now()->subDays(30)),
            'renk'   => 'rose',
            'ikon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>',
        ]]);
    }

    /**
     * Son 48 saat içinde iptal edilen randevular.
     */
    private function bugunIptalEdilenler(Doktor $doktor): Collection
    {
        $ikiGunOnce = Carbon::now()->subDays(2);

        return $doktor->randevular()
            ->where('durum', 'iptal')
            ->where('updated_at', '>=', $ikiGunOnce)
            ->orderByDesc('updated_at')
            ->take(10)
            ->get()
            ->map(function ($r) {
                $adSoyad = trim("{$r->ad} {$r->soyad}") ?: 'İsim yok';
                return [
                    'id'     => "randevu_iptal_{$r->id}",
                    'tip'    => 'randevu_iptal',
                    'onem'   => 'uyari',
                    'baslik' => 'Randevu iptal edildi',
                    'mesaj'  => "{$adSoyad} — " . Carbon::parse($r->tarih)->format('d.m.Y') . ' ' . substr((string) $r->saat, 0, 5) . ' saati boşaldı.',
                    'url'    => route('hekim.randevu.takvim'),
                    'tarih'  => $r->updated_at ?? Carbon::now(),
                    'renk'   => 'rose',
                    'ikon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ];
            });
    }

    /**
     * Onay bekleyen yorumlar.
     */
    private function onayBekleyenYorumlar(Doktor $doktor): Collection
    {
        return $doktor->yorumlar()
            ->where('onay_durumu', 'beklemede')
            ->with('hasta:id,ad,soyad')
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($y) {
                $hastaAd = $y->hasta?->ad_soyad ?: 'Anonim';
                $yorum = str($y->yorum ?? '')->limit(60);
                return [
                    'id'     => "yorum_{$y->id}",
                    'tip'    => 'yorum_bekliyor',
                    'onem'   => 'uyari',
                    'baslik' => 'Onay bekleyen yorum',
                    'mesaj'  => "{$hastaAd}: {$yorum}",
                    'url'    => route('hekim.yorumlar.index'),
                    'tarih'  => $y->created_at ?? Carbon::now(),
                    'renk'   => 'indigo',
                    'ikon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>',
                ];
            });
    }

    /**
     * Bugün için kalan (henüz saati geçmemiş) onaylanmış randevular.
     */
    private function bugunkuRandevular(Doktor $doktor): Collection
    {
        $simdi = Carbon::now();
        $bugun = $simdi->toDateString();
        $simdiSaat = $simdi->format('H:i:s');

        return $doktor->randevular()
            ->where('durum', 'onaylandi')
            ->whereDate('tarih', $bugun)
            ->where('saat', '>=', $simdiSaat)
            ->orderBy('saat')
            ->take(10)
            ->get()
            ->map(function ($r) {
                $adSoyad = trim("{$r->ad} {$r->soyad}") ?: 'İsim yok';
                return [
                    'id'     => "randevu_bugun_{$r->id}",
                    'tip'    => 'randevu_bugun',
                    'onem'   => 'bilgi',
                    'baslik' => 'Bugünkü randevu',
                    'mesaj'  => substr((string) $r->saat, 0, 5) . " — {$adSoyad}",
                    'url'    => route('hekim.randevu.takvim'),
                    'tarih'  => Carbon::parse($r->tarih . ' ' . $r->saat),
                    'renk'   => 'emerald',
                    'ikon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ];
            });
    }
}
