<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Database\Seeder;

class KlinikSeeder extends Seeder
{
    /**
     * Klinik paketleri.
     * Web sitesi yetkisi (klinik_web_sitesi) YALNIZCA en yüksek paket: Klinik Kurumsal.
     */
    public function run(): void
    {
        // Özellik kaydı (PaketSeeder de oluşturur; idempotent)
        $webOzellik = PaketOzelligi::updateOrCreate(
            ['kod' => 'klinik_web_sitesi'],
            [
                'ad' => 'Klinik Web Sitesi',
                'aciklama' => 'Klinik markalı özel web sitesi, çok hekim vitrini ve online randevu (yalnızca Klinik Kurumsal).',
            ]
        );

        Paket::where('tur', 'klinik')->delete();

        // Klinik Başlangıç — web YOK
        $baslangic = Paket::create([
            'ad' => 'Klinik Başlangıç',
            'tur' => 'klinik',
            'aciklama' => 'Küçük klinikler ve muayenehaneler için ideal başlangıç paketi.',
            'aylik_fiyat' => 1899.00,
            'aylik_indirimli_fiyat' => 1399.00,
            'yillik_fiyat' => 18990.00,
            'yillik_indirimli_fiyat' => 13990.00,
            'ozellikler' => [
                'Ortak Hasta Havuzu ve CRM',
                'Maksimum 3 Aktif Hekim Tanımlama',
                '1 Sekreter / Personel Hesabı',
                'Hekim Çalışma Saatleri ve Takvim Yönetimi',
                'Temel Raporlama Modülü',
                'Klinik Duyuru Sistemi',
            ],
            'max_doktor_sayisi' => 3,
            'max_personel_sayisi' => 1,
            'max_hasta_sayisi' => null,
            'max_randevu_sayisi' => null,
            'merkezi_finans_mi' => false,
            'toplu_randevu_mi' => false,
            'raporlama_mi' => false,
            'hasta_havuzu_mi' => true,
            'sira' => 1,
            'aktif_mi' => true,
        ]);
        $baslangic->sistemOzellikleri()->sync([]);

        // Klinik Profesyonel — web YOK
        $profesyonel = Paket::create([
            'ad' => 'Klinik Profesyonel',
            'tur' => 'klinik',
            'aciklama' => 'Orta ölçekli klinikler ve tıp merkezleri için gelişmiş özellikler.',
            'aylik_fiyat' => 3699.00,
            'aylik_indirimli_fiyat' => 2699.00,
            'yillik_fiyat' => 36990.00,
            'yillik_indirimli_fiyat' => 26990.00,
            'ozellikler' => [
                'Klinik Başlangıç Özelliklerinin Tümü',
                'Maksimum 10 Aktif Hekim Tanımlama',
                '5 Sekreter / Personel Hesabı',
                'Toplu Randevu Yönetimi ve Bloklama',
                'Merkezi Finans & Gelir-Gider Muhasebesi',
                'Gelişmiş Hakediş ve Komisyon Hesaplama Modülü',
                'Detaylı Klinik Performans Raporlama',
            ],
            'max_doktor_sayisi' => 10,
            'max_personel_sayisi' => 5,
            'max_hasta_sayisi' => null,
            'max_randevu_sayisi' => null,
            'merkezi_finans_mi' => true,
            'toplu_randevu_mi' => true,
            'raporlama_mi' => true,
            'hasta_havuzu_mi' => true,
            'sira' => 2,
            'aktif_mi' => true,
        ]);
        $profesyonel->sistemOzellikleri()->sync([]);

        // Klinik Kurumsal — ÖZEL WEB SİTESİ DAHİL (tek paket; klinik_web_sitesi yetkisi)
        $kurumsal = Paket::create([
            'ad' => 'Klinik Kurumsal',
            'tur' => 'klinik',
            'aciklama' => 'Sınırsız hekim/personel + özel klinik web sitesi: kurumsal domain, çok hekimli vitrin, CMS ve online randevu tek pakette.',
            'aylik_fiyat' => 5499.00,
            'aylik_indirimli_fiyat' => 3999.00,
            'yillik_fiyat' => 54990.00,
            'yillik_indirimli_fiyat' => 39990.00,
            'ozellikler' => [
                'Klinik Profesyonel Özelliklerinin Tümü',
                'Sınırsız Hekim Ekleme Yetkisi',
                'Sınırsız Sekreter / Personel Tanımlama',
                'Merkezi Finans & PDF Rapor Çıktıları',
                'Tüm Şubeler İçin Ortak Hasta Havuzu',
                'Özel Klinik Web Sitesi (domain + CMS + hosting + SSL)',
                'Premium site temaları (Sıcak, Ocean) + 3 ücretsiz tema',
                'Çok hekimli vitrin ve hekim seçimli online randevu',
                'Öncelikli Canlı Destek & Sekreterya Eğitimi',
                '7/24 Teknik Altyapı ve Sunucu Desteği',
            ],
            'max_doktor_sayisi' => 999,
            'max_personel_sayisi' => 999,
            'max_hasta_sayisi' => null,
            'max_randevu_sayisi' => null,
            'merkezi_finans_mi' => true,
            'toplu_randevu_mi' => true,
            'raporlama_mi' => true,
            'hasta_havuzu_mi' => true,
            'sira' => 3,
            'aktif_mi' => true,
        ]);
        $kurumsal->sistemOzellikleri()->sync([$webOzellik->id]);
    }
}
