<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteAyari;

class SiteAyariSeeder extends Seeder
{
    public function run(): void
    {
        SiteAyari::updateOrCreate(
            ['id' => 1],
            [
                // ── SEO Meta ─────────────────────────────────────────────────────
                // Sayfa bazlı @section('baslik') yoksa bu değer kullanılır (fallback)
                // Anasayfa başlığı SeoMeta::homeTitle() ile ayrıca belirleniyor.
                'meta_baslik'           => 'Online Doktor Randevusu Al | 90+ Branş | Randevu Ajandam',

                // 155 karakter — Google 160 px sınırı içinde, doğal sonlanıyor
                'meta_aciklama'         => 'Türkiye genelinde 90+ uzmanlık alanında online doktor ve klinik randevusu alın. Diyetisyen, psikolog, kardiyolog, diş hekimi — anında randevu, 7/24 hizmet.',

                // Google keywords meta'yı sıralama sinyali olarak kullanmaz;
                // Yandex ve Bing bazı ağırlık verir. B2C + B2B karışımı tutuldu.
                'meta_anahtar_kelimeler'=> 'online doktor randevusu, doktor randevu al, hekim randevu, klinik randevu, psikolog randevusu, diyetisyen randevusu, diş hekimi randevusu, kardiyoloji randevu, online psikolog, uzman doktor bul, randevu yazılımı, randevu ajandam',
                'meta_yazar'            => 'Randevu Ajandam',

                // ── Analitik & Reklam ─────────────────────────────────────────────
                // Bu değerler /yonetim/seo panelinden de güncellenebilir.
                // Eğer GTM kullanıyorsanız GA4 ve Meta Pixel'i GTM üzerinden yönetin.
                'gtm_container_id'      => '',   // GTM-XXXXXXX
                'ga4_measurement_id'    => '',   // G-XXXXXXXXXX
                'meta_pixel_id'         => '',   // Meta Business Manager → Events Manager
                'google_ads_id'         => '',   // AW-XXXXXXXXX

                // ── reCAPTCHA v3 ──────────────────────────────────────────────────
                // Anahtarlar .env üzerinden de verilir (RECAPTCHA_SITE_KEY / _SECRET_KEY)
                // Anahtarlar boşsa false bırakın.
                'recaptcha_enabled'     => false,
                'recaptcha_site_key'    => '',
                'recaptcha_secret_key'  => '',

                // ── Banka / EFT ───────────────────────────────────────────────────
                // Paket ödemelerinde banka havalesi seçeneği için
                'banka_adi'             => '',
                'banka_hesap_sahibi'    => '',
                'banka_iban'            => '',
                'banka_aciklama'        => 'Havale açıklamasına lütfen kayıtlı e-posta adresinizi yazınız.',

                // ── Ödeme entegrasyonları (iyzico / PayTR) ───────────────────────
                // Hassas veriler — .env üzerinden verilmesi önerilir.
                // Seeder'a girmeyin; /yonetim/paket-ayarlari panelinden doldurun.
                // 'iyzico_api_key'     => '',
                // 'iyzico_secret_key'  => '',
                // 'iyzico_base_url'    => 'https://sandbox-api.iyzipay.com',
            ]
        );
    }
}
