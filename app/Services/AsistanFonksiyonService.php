<?php

namespace App\Services;

use App\Models\BeklemeListesi;
use App\Models\Blog;
use App\Models\Doktor;
use App\Models\DoktorIzin;
use App\Models\Hizmet;
use App\Models\Randevu;
use Carbon\Carbon;

class AsistanFonksiyonService
{
    public function __construct(protected SlotService $slotService) {}

    public function randevu_listele(int $doktorId, string $tarihBaslangic, string $tarihBitis, ?string $durum = null): array
    {
        $q = Randevu::where('doktor_id', $doktorId)
            ->whereBetween('tarih', [
                Carbon::parse($tarihBaslangic)->toDateString(),
                Carbon::parse($tarihBitis)->toDateString(),
            ])
            ->with('hizmet')
            ->orderBy('tarih')
            ->orderBy('saat');

        if ($durum && in_array($durum, ['beklemede', 'onaylandi', 'tamamlandi', 'iptal'], true)) {
            $q->where('durum', $durum);
        } else {
            $q->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi', 'iptal']);
        }

        $randevular = $q->get();

        return [
            'toplam' => $randevular->count(),
            'randevular' => $randevular->map(fn ($r) => [
                'id'        => $r->id,
                'tarih'     => Carbon::parse($r->tarih)->format('d.m.Y'),
                'gun'       => Carbon::parse($r->tarih)->translatedFormat('l'),
                'saat'      => substr((string) $r->saat, 0, 5),
                'durum'     => $r->durum,
                'hizmet'    => $r->hizmet?->ad ?? 'Belirtilmemiş',
            ])->values()->all(),
        ];
    }

    public function bos_saat_bul(int $doktorId, string $tarih): array
    {
        $doktor = Doktor::with(['calismaSaatleri', 'randevuAyari'])->find($doktorId);
        if (! $doktor) {
            return ['hata' => 'Doktor bulunamadı'];
        }

        $gun = Carbon::parse($tarih);

        $randevular = $doktor->randevular()
            ->whereDate('tarih', $gun)
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
            ->get();

        $izinler = $doktor->izinler()
            ->where('baslangic_zaman', '<=', $gun->copy()->endOfDay())
            ->where('bitis_zaman', '>=', $gun->copy()->startOfDay())
            ->get();

        $periyot = $this->slotService->getPeriyot($doktor);
        $slots = $this->slotService->generateGunlukSlotlar($doktor, $gun, $randevular, $izinler, $periyot);

        $bosSlotlar = array_values(array_filter($slots, fn ($s) => in_array($s['durum'] ?? '', ['bos', 'musait'], true)));

        return [
            'tarih'       => $gun->format('d.m.Y'),
            'gun'         => $gun->translatedFormat('l'),
            'bos_slot_sayisi' => count($bosSlotlar),
            'bos_saatler' => array_map(fn ($s) => $s['saat_string'], $bosSlotlar),
        ];
    }

    public function randevu_tasi(int $doktorId, int $randevuId, string $yeniTarih, string $yeniSaat): array
    {
        $randevu = Randevu::where('doktor_id', $doktorId)->find($randevuId);
        if (! $randevu) {
            return ['basari' => false, 'hata' => 'Randevu bulunamadı'];
        }

        $tarihStr = Carbon::parse($yeniTarih)->toDateString();
        $saatStr  = substr($yeniSaat, 0, 5);

        $mevcut = Randevu::where('doktor_id', $doktorId)
            ->where('tarih', $tarihStr)
            ->where('saat', $saatStr . ':00')
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->where('id', '!=', $randevuId)
            ->exists();

        if ($mevcut) {
            return ['basari' => false, 'hata' => 'Bu tarih ve saatte başka bir randevu mevcut'];
        }

        $randevu->tarih = $tarihStr;
        $randevu->saat  = $saatStr . ':00';
        $randevu->save();

        return [
            'basari'     => true,
            'randevu_id' => $randevuId,
            'yeni_tarih' => Carbon::parse($tarihStr)->format('d.m.Y'),
            'yeni_saat'  => $saatStr,
        ];
    }

    public function randevu_olustur(int $doktorId, string $tarih, string $saat, ?int $hizmetId = null, string $not = ''): array
    {
        $doktor = Doktor::find($doktorId);
        if (! $doktor) {
            return ['basari' => false, 'hata' => 'Doktor bulunamadı'];
        }

        $tarihStr = Carbon::parse($tarih)->toDateString();
        $saatStr  = substr($saat, 0, 5);

        $mevcut = Randevu::where('doktor_id', $doktorId)
            ->where('tarih', $tarihStr)
            ->where('saat', $saatStr . ':00')
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->exists();

        if ($mevcut) {
            return ['basari' => false, 'hata' => 'Bu saatte zaten bir randevu mevcut'];
        }

        $randevu = Randevu::create([
            'doktor_id'  => $doktorId,
            'hizmet_id'  => $hizmetId,
            'tarih'      => $tarihStr,
            'saat'       => $saatStr . ':00',
            'durum'      => 'beklemede',
            'not'        => $not ?: null,
            'ad'         => 'Asistan',
            'soyad'      => 'Randevu',
            'telefon'    => '0000000000',
            'e_posta'    => 'asistan@randevu.local',
        ]);

        return [
            'basari'     => true,
            'randevu_id' => $randevu->id,
            'tarih'      => Carbon::parse($tarihStr)->format('d.m.Y'),
            'saat'       => $saatStr,
            'mesaj'      => 'Randevu oluşturuldu. Hasta bilgilerini takvimden tamamlayabilirsiniz.',
        ];
    }

    public function takvim_cakisma_kontrol(int $doktorId, string $baslangicZaman, string $bitisZaman): array
    {
        $bas = Carbon::parse($baslangicZaman);
        $bit = Carbon::parse($bitisZaman);

        $randevular = Randevu::where('doktor_id', $doktorId)
            ->whereBetween('tarih', [$bas->toDateString(), $bit->toDateString()])
            ->whereIn('durum', ['beklemede', 'onaylandi'])
            ->with('hizmet')
            ->orderBy('tarih')
            ->orderBy('saat')
            ->get();

        // If full-day block, all appointments on those dates qualify.
        // If partial time block, additionally filter by time overlap.
        $basSaatTam = $bas->format('H:i') === '00:00' && $bit->format('H:i') === '23:59';
        if (! $basSaatTam) {
            $randevular = $randevular->filter(function ($r) use ($bas, $bit) {
                if ($r->tarih->toDateString() === $bas->toDateString()) {
                    $saat = substr((string) $r->saat, 0, 5);
                    return $saat >= $bas->format('H:i') && $saat < $bit->format('H:i');
                }
                return true;
            });
        }

        return [
            'etkilenen_sayi' => $randevular->count(),
            'randevular'     => $randevular->map(fn ($r) => [
                'id'     => $r->id,
                'tarih'  => Carbon::parse($r->tarih)->format('d.m.Y'),
                'gun'    => Carbon::parse($r->tarih)->translatedFormat('l'),
                'saat'   => substr((string) $r->saat, 0, 5),
                'hizmet' => $r->hizmet?->ad ?? 'Belirtilmemiş',
                'durum'  => $r->durum,
            ])->values()->all(),
        ];
    }

    public function takvim_kapat_ve_iptal(int $doktorId, string $baslangicZaman, string $bitisZaman, string $aciklama = '', string $secim = 'kapat_ve_iptal'): array
    {
        $kontrol      = $this->takvim_cakisma_kontrol($doktorId, $baslangicZaman, $bitisZaman);
        $iptalSayisi  = 0;
        $beklemeSayisi = 0;

        foreach ($kontrol['randevular'] as $r) {
            $randevu = Randevu::where('doktor_id', $doktorId)->with('hasta', 'hizmet')->find($r['id']);
            if (! $randevu) {
                continue;
            }

            $randevu->durum = 'iptal';
            $randevu->save();
            $iptalSayisi++;

            // SMS bildirimi
            if ($secim === 'kapat_iptal_sms' && $randevu->telefon && config('sms.driver', 'log') !== 'log') {
                try {
                    $mesaj = "Sayın {$randevu->ad}, {$randevu->tarih->format('d.m.Y')} tarihli " . substr((string) $randevu->saat, 0, 5) . " saatindeki randevunuz iptal edilmiştir. Yeni randevu için lütfen bizimle iletişime geçin.";
                    app(\App\Services\SmsService::class)->send($randevu->telefon, $mesaj);
                } catch (\Throwable) {
                    // SMS hatası işlemi bozmasın
                }
            }

            // Bekleme listesine ekle
            if ($secim === 'kapat_bekleme') {
                try {
                    BeklemeListesi::create([
                        'doktor_id'    => $doktorId,
                        'hasta_id'     => $randevu->hasta_id,
                        'hizmet_id'    => $randevu->hizmet_id,
                        'ad'           => $randevu->ad,
                        'soyad'        => $randevu->soyad,
                        'telefon'      => $randevu->telefon,
                        'e_posta'      => $randevu->e_posta,
                        'tercih_tarih' => $randevu->tarih,
                        'not'          => 'Asistan tarafından takvim kapatma nedeniyle eklendi.',
                        'durum'        => 'beklemede',
                    ]);
                    $beklemeSayisi++;
                } catch (\Throwable) {
                }
            }
        }

        $izinSonuc                  = $this->takvim_kapat($doktorId, $baslangicZaman, $bitisZaman, $aciklama);
        $izinSonuc['iptal_edilen']  = $iptalSayisi;
        $izinSonuc['bekleme_eklenen'] = $beklemeSayisi;
        $izinSonuc['secim']         = $secim;
        return $izinSonuc;
    }

    public function takvim_ac(int $doktorId, string $tarih): array
    {
        $gun = Carbon::parse($tarih)->toDateString();

        // Find all izin blocks that overlap this date
        $izinler = DoktorIzin::where('doktor_id', $doktorId)
            ->where('baslangic_zaman', '<=', $gun . ' 23:59:59')
            ->where('bitis_zaman',     '>=', $gun . ' 00:00:00')
            ->get();

        if ($izinler->isEmpty()) {
            return ['basari' => false, 'hata' => Carbon::parse($gun)->format('d.m.Y') . ' tarihinde kapatılmış takvim bloğu bulunamadı.'];
        }

        $silinen = $izinler->count();
        $izinler->each->delete();

        return [
            'basari'  => true,
            'tarih'   => Carbon::parse($gun)->format('d.m.Y'),
            'silinen' => $silinen,
            'mesaj'   => Carbon::parse($gun)->format('d.m.Y') . ' tarihindeki ' . $silinen . ' takvim bloğu kaldırıldı. Randevu alımı tekrar açık.',
        ];
    }

    public function takvim_kapat(int $doktorId, string $baslangicZaman, string $bitisZaman, string $aciklama = ''): array
    {
        $bas = Carbon::parse($baslangicZaman);
        $bit = Carbon::parse($bitisZaman);

        if ($bit->lte($bas)) {
            return ['basari' => false, 'hata' => 'Bitiş zamanı başlangıçtan önce olamaz'];
        }

        $izin = DoktorIzin::create([
            'doktor_id'       => $doktorId,
            'baslangic_zaman' => $bas,
            'bitis_zaman'     => $bit,
            'aciklama'        => $aciklama ?: 'Asistan tarafından kapatıldı',
        ]);

        return [
            'basari'           => true,
            'izin_id'          => $izin->id,
            'baslangic'        => $bas->format('d.m.Y H:i'),
            'bitis'            => $bit->format('d.m.Y H:i'),
            'mesaj'            => 'Takvim bloğu oluşturuldu.',
        ];
    }

    public function randevu_durum_guncelle(int $doktorId, int $randevuId, string $yeniDurum): array
    {
        $gecerliDurumlar = ['onaylandi', 'tamamlandi', 'iptal', 'beklemede'];
        if (! in_array($yeniDurum, $gecerliDurumlar, true)) {
            return ['basari' => false, 'hata' => 'Geçersiz durum. Kabul edilen değerler: ' . implode(', ', $gecerliDurumlar)];
        }

        $randevu = Randevu::where('doktor_id', $doktorId)->find($randevuId);
        if (! $randevu) {
            return ['basari' => false, 'hata' => 'Randevu bulunamadı'];
        }

        $eskiDurum = $randevu->durum;
        $randevu->durum = $yeniDurum;
        $randevu->save();

        return [
            'basari'      => true,
            'randevu_id'  => $randevuId,
            'eski_durum'  => $eskiDurum,
            'yeni_durum'  => $yeniDurum,
            'tarih'       => Carbon::parse($randevu->tarih)->format('d.m.Y'),
            'saat'        => substr((string) $randevu->saat, 0, 5),
        ];
    }

    public function randevular_durum_toplu_guncelle(int $doktorId, array $randevuIdler, string $yeniDurum): array
    {
        $gecerliDurumlar = ['onaylandi', 'tamamlandi', 'iptal', 'beklemede'];
        if (! in_array($yeniDurum, $gecerliDurumlar, true)) {
            return ['basari' => false, 'hata' => 'Geçersiz durum: ' . $yeniDurum];
        }

        $guncellenen = 0;
        $hatalar     = [];

        foreach ($randevuIdler as $id) {
            $randevu = Randevu::where('doktor_id', $doktorId)->find((int) $id);
            if (! $randevu) {
                $hatalar[] = (int) $id;
                continue;
            }
            $randevu->durum = $yeniDurum;
            $randevu->save();
            $guncellenen++;
        }

        return [
            'basari'      => $guncellenen > 0,
            'guncellenen' => $guncellenen,
            'yeni_durum'  => $yeniDurum,
            'hata_idler'  => $hatalar,
        ];
    }

    public function bekleme_listesi_goster(int $doktorId, ?string $durum = null): array
    {
        $q = BeklemeListesi::where('doktor_id', $doktorId)->with('hizmet')->orderBy('created_at');

        if ($durum && in_array($durum, ['beklemede', 'bildirildi', 'tamamlandi'], true)) {
            $q->where('durum', $durum);
        }

        $liste = $q->get();

        return [
            'toplam' => $liste->count(),
            'liste'  => $liste->map(fn ($b) => [
                'id'           => $b->id,
                'ad'           => $b->ad,
                'tercih_tarih' => $b->tercih_tarih ? Carbon::parse($b->tercih_tarih)->format('d.m.Y') : 'Belirtilmemiş',
                'durum'        => $b->durum,
                'hizmet'       => optional($b->hizmet)->ad ?? 'Belirtilmemiş',
            ])->values()->all(),
        ];
    }

    public function randevu_notu_guncelle(int $doktorId, int $randevuId, string $not): array
    {
        $randevu = Randevu::where('doktor_id', $doktorId)->find($randevuId);
        if (! $randevu) {
            return ['basari' => false, 'hata' => 'Randevu bulunamadı'];
        }

        $randevu->not = $not ?: null;
        $randevu->save();

        return [
            'basari'     => true,
            'randevu_id' => $randevuId,
            'tarih'      => Carbon::parse($randevu->tarih)->format('d.m.Y'),
            'saat'       => substr((string) $randevu->saat, 0, 5),
            'not'        => $not,
        ];
    }

    public function hizmetleri_listele(int $doktorId): array
    {
        $hizmetler = Hizmet::where('doktor_id', $doktorId)->where('aktif_mi', true)->orderBy('ad')->get();

        return [
            'toplam'    => $hizmetler->count(),
            'hizmetler' => $hizmetler->map(fn ($h) => [
                'id'    => $h->id,
                'ad'    => $h->ad,
                'sure'  => isset($h->sure)  ? $h->sure . ' dk' : null,
                'fiyat' => isset($h->fiyat) ? $h->fiyat . ' ₺'  : null,
            ])->values()->all(),
        ];
    }

    public function hasta_randevulari(int $doktorId, string $aramaMetni): array
    {
        $randevular = Randevu::where('doktor_id', $doktorId)
            ->where(function ($q) use ($aramaMetni) {
                $q->where('ad', 'like', '%' . $aramaMetni . '%')
                  ->orWhere('soyad', 'like', '%' . $aramaMetni . '%');
            })
            ->with('hizmet')
            ->orderBy('tarih', 'desc')
            ->orderBy('saat', 'desc')
            ->limit(20)
            ->get();

        return [
            'arama'      => $aramaMetni,
            'toplam'     => $randevular->count(),
            'randevular' => $randevular->map(fn ($r) => [
                'id'     => $r->id,
                'tarih'  => Carbon::parse($r->tarih)->format('d.m.Y'),
                'gun'    => Carbon::parse($r->tarih)->translatedFormat('l'),
                'saat'   => substr((string) $r->saat, 0, 5),
                'durum'  => $r->durum,
                'hizmet' => $r->hizmet?->ad ?? 'Belirtilmemiş',
            ])->values()->all(),
        ];
    }

    public function profil_seo_incele(int $doktorId): array
    {
        $doktor = Doktor::with(['il', 'ilce', 'branslar'])->find($doktorId);
        if (! $doktor) {
            return ['hata' => 'Profil bulunamadı'];
        }

        $biyografi     = $doktor->biyografi ?? '';
        $branslar      = $doktor->branslar?->pluck('ad')->join(', ') ?? '';
        $sosyalMedya   = array_filter([
            'instagram' => $doktor->instagram,
            'facebook'  => $doktor->facebook,
            'twitter'   => $doktor->twitter,
            'linkedin'  => $doktor->linkedin,
            'youtube'   => $doktor->youtube,
        ]);

        return [
            'ad_soyad'              => $doktor->ad_soyad,
            'unvan'                 => $doktor->unvan,
            'uzmanlik_alani'        => $doktor->uzmanlik_alani,
            'branslar'              => $branslar,
            'il'                    => optional($doktor->il)->ad,
            'ilce'                  => optional($doktor->ilce)->ad,
            'biyografi'             => mb_substr(strip_tags($biyografi), 0, 800),
            'biyografi_karakter'    => mb_strlen(strip_tags($biyografi)),
            'slug'                  => $doktor->slug,
            'web_sitesi'            => $doktor->web_sitesi,
            'sosyal_medya'          => $sosyalMedya,
            'sosyal_medya_sayisi'   => count($sosyalMedya),
            'profil_resmi_var_mi'   => ! empty($doktor->profil_resmi),
            'platformda_gorunur'    => (bool) $doktor->platformda_gorunur,
            'seo_kontrol_noktalari' => [
                'biyografi_yeterli'  => mb_strlen(strip_tags($biyografi)) >= 150,
                'uzmanlik_dolu'      => ! empty($doktor->uzmanlik_alani),
                'profil_resmi_var'   => ! empty($doktor->profil_resmi),
                'sosyal_medya_var'   => count($sosyalMedya) > 0,
                'web_sitesi_var'     => ! empty($doktor->web_sitesi),
            ],
        ];
    }

    public function blog_seo_incele(int $doktorId, ?int $blogId = null): array
    {
        $q = Blog::where('doktor_id', $doktorId)->orderBy('created_at', 'desc');
        if ($blogId) {
            $q->where('id', $blogId);
        }

        $bloglar = $q->limit(10)->get();

        return [
            'toplam' => $bloglar->count(),
            'bloglar' => $bloglar->map(fn ($b) => [
                'id'                      => $b->id,
                'baslik'                  => $b->baslik,
                'baslik_karakter'         => mb_strlen($b->baslik ?? ''),
                'meta_baslik'             => $b->meta_baslik,
                'meta_baslik_karakter'    => mb_strlen($b->meta_baslik ?? ''),
                'meta_aciklama'           => $b->meta_aciklama,
                'meta_aciklama_karakter'  => mb_strlen($b->meta_aciklama ?? ''),
                'meta_anahtar_kelimeler'  => $b->meta_anahtar_kelimeler,
                'icerik_ozet'             => mb_substr(strip_tags($b->icerik ?? ''), 0, 400),
                'icerik_karakter'         => mb_strlen(strip_tags($b->icerik ?? '')),
                'aktif_mi'                => (bool) $b->aktif_mi,
                'okunma_sayisi'           => $b->okunma_sayisi ?? 0,
                'slug'                    => $b->slug,
                'seo_kontrol_noktalari'   => [
                    'meta_baslik_var'        => ! empty($b->meta_baslik),
                    'meta_baslik_uzunluk_ok' => mb_strlen($b->meta_baslik ?? '') >= 30 && mb_strlen($b->meta_baslik ?? '') <= 60,
                    'meta_aciklama_var'      => ! empty($b->meta_aciklama),
                    'meta_aciklama_uzunluk_ok' => mb_strlen($b->meta_aciklama ?? '') >= 120 && mb_strlen($b->meta_aciklama ?? '') <= 160,
                    'anahtar_kelime_var'     => ! empty($b->meta_anahtar_kelimeler),
                    'icerik_yeterli'         => mb_strlen(strip_tags($b->icerik ?? '')) >= 300,
                ],
            ])->values()->all(),
        ];
    }

    public function ozet_ver(int $doktorId, string $periyot = 'bugun'): array
    {
        $bugun = Carbon::today();

        if ($periyot === 'hafta') {
            $bas = $bugun->copy()->startOfWeek();
            $bit = $bugun->copy()->endOfWeek();
            $etiket = 'Bu hafta';
        } elseif ($periyot === 'ay') {
            $bas = $bugun->copy()->startOfMonth();
            $bit = $bugun->copy()->endOfMonth();
            $etiket = 'Bu ay';
        } else {
            $bas = $bugun->copy();
            $bit = $bugun->copy();
            $etiket = 'Bugün';
        }

        $randevular = Randevu::where('doktor_id', $doktorId)
            ->whereBetween('tarih', [$bas->toDateString(), $bit->toDateString()])
            ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi', 'iptal'])
            ->selectRaw('durum, count(*) as adet')
            ->groupBy('durum')
            ->pluck('adet', 'durum')
            ->toArray();

        $toplam     = array_sum($randevular);
        $beklemede  = $randevular['beklemede']  ?? 0;
        $onaylandi  = $randevular['onaylandi']  ?? 0;
        $tamamlandi = $randevular['tamamlandi'] ?? 0;
        $iptal      = $randevular['iptal']      ?? 0;

        return [
            'periyot'    => $etiket,
            'toplam'     => $toplam,
            'beklemede'  => $beklemede,
            'onaylandi'  => $onaylandi,
            'tamamlandi' => $tamamlandi,
            'iptal'      => $iptal,
        ];
    }
}
