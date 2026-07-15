<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorCalismaSaati;
use App\Models\Hizmet;
use App\Models\RandevuAyari;
use Illuminate\Database\Seeder;

class RandevuAjandamDisHekimiSeeder extends Seeder
{
    public function run(): void
    {
        $doktor = Doktor::query()->where('e_posta', 'hekim@randevuajandam.com')->firstOrFail();
        $brans = Brans::query()->firstOrCreate(
            ['ad' => 'Genel Diş Hekimliği']
        );

        $doktor->update([
            'ad_soyad' => 'Randevu Ajandam',
            'unvan' => 'Dt.',
            'uzmanlik_alani' => 'Genel Diş Hekimliği',
            'klinik_adi' => 'Randevu Ajandam Diş Kliniği',
            'biyografi' => '<p>Dt. Randevu Ajandam, koruyucu diş hekimliği ve estetik gülüş uygulamalarında kişiye özel, anlaşılır ve güven odaklı bir yaklaşım sunar.</p><p>Her hastanın ihtiyacını detaylı değerlendirmek; sağlıklı, fonksiyonel ve doğal bir gülüşe ulaşmasına yardımcı olmak önceliğimizdir.</p>',
            'mezuniyet' => ['Genel Diş Hekimliği', 'Koruyucu ve Estetik Diş Hekimliği Uygulamaları'],
            'aktif_mi' => true,
            'platformda_gorunur' => true,
        ]);
        $doktor->branslar()->sync([$brans->id]);

        $hizmetler = [
            ['ad' => 'Genel Diş Muayenesi', 'sure' => 30, 'fiyat' => 0, 'aciklama' => 'Ağız ve diş sağlığınızın kapsamlı değerlendirilmesi, tedavi planlaması ve koruyucu bakım önerileri.'],
            ['ad' => 'Diş Taşı Temizliği', 'sure' => 30, 'fiyat' => 0, 'aciklama' => 'Diş eti sağlığını destekleyen, plak ve diş taşı birikimlerini temizleyen profesyonel bakım uygulaması.'],
            ['ad' => 'Estetik Dolgu', 'sure' => 45, 'fiyat' => 0, 'aciklama' => 'Diş renginizle uyumlu kompozit materyaller kullanılarak yapılan doğal görünümlü restorasyon.'],
            ['ad' => 'Diş Beyazlatma', 'sure' => 45, 'fiyat' => 0, 'aciklama' => 'Diş minesine uygun yöntemlerle daha aydınlık ve doğal görünümlü bir gülüş hedefleyen uygulama.'],
            ['ad' => 'Kanal Tedavisi', 'sure' => 60, 'fiyat' => 0, 'aciklama' => 'Enfekte veya hasar görmüş diş dokusunun korunmasına yardımcı olan endodontik tedavi.'],
            ['ad' => 'Zirkonyum Kaplama', 'sure' => 45, 'fiyat' => 0, 'aciklama' => 'Dayanıklılık ve estetik beklentilerini birlikte karşılamayı hedefleyen zirkonyum destekli kaplama planlaması.'],
        ];
        foreach ($hizmetler as $hizmet) {
            Hizmet::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'ad' => $hizmet['ad']],
                array_merge($hizmet, [
                    'aktif_mi' => true,
                    'meta_baslik' => $hizmet['ad'].' | Dt. Randevu Ajandam',
                    'meta_aciklama' => $hizmet['aciklama'],
                    'meta_anahtar_kelimeler' => 'genel diş hekimi, diş sağlığı, '.$hizmet['ad'],
                ])
            );
        }

        foreach ([
            ['baslik' => 'Diş Sağlığını Korumak İçin 5 Günlük Alışkanlık', 'icerik' => '<p>Düzenli fırçalama, diş ipi kullanımı, dengeli beslenme ve rutin kontroller ağız sağlığının temelini oluşturur.</p>', 'resim' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=1000&q=80'],
            ['baslik' => 'Diş Taşı Temizliği Neden Önemlidir?', 'icerik' => '<p>Profesyonel temizlik, diş eti sağlığını destekler ve günlük bakımın ulaşamadığı birikimlerin uzaklaştırılmasına yardımcı olur.</p>', 'resim' => 'https://images.unsplash.com/photo-1606811971618-4486d14f3f99?auto=format&fit=crop&w=1000&q=80'],
            ['baslik' => 'Estetik Dolgu Hakkında Merak Edilenler', 'icerik' => '<p>Kompozit dolgular, uygun vakalarda diş rengiyle uyumlu ve doğal görünümlü bir restorasyon seçeneği sunar.</p>', 'resim' => 'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?auto=format&fit=crop&w=1000&q=80'],
        ] as $blog) {
            Blog::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'baslik' => $blog['baslik']],
                array_merge($blog, ['aktif_mi' => true, 'meta_baslik' => $blog['baslik'].' | Dt. Randevu Ajandam'])
            );
        }

        RandevuAyari::query()->updateOrCreate(['doktor_id' => $doktor->id], [
            'randevu_onay_tipi' => 'manuel',
            'randevu_periyodu' => 30,
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
            'randevu_iptal_aktif_mi' => true,
            'iptal_saat_limiti' => 24,
            'gunluk_maksimum_randevu' => 0,
            'email_bildirimleri' => true,
            'sms_bildirimleri' => false,
            'aktif_mi' => true,
        ]);

        foreach (range(1, 7) as $gun) {
            DoktorCalismaSaati::query()->updateOrCreate(
                ['doktor_id' => $doktor->id, 'gun' => $gun],
                [
                    'aktif_mi' => $gun <= 6,
                    'mesai_baslangic' => '09:00:00',
                    'mesai_bitis' => $gun === 6 ? '14:00:00' : '18:00:00',
                    'ogle_arasi_aktif_mi' => $gun <= 5,
                    'ogle_baslangic' => '12:30:00',
                    'ogle_bitis' => '13:30:00',
                ]
            );
        }
    }
}
