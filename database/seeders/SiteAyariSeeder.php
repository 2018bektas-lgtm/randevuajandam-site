<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteAyari;

class SiteAyariSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SiteAyari::create([
            'meta_baslik' => 'Online Doktor Randevusu | Hekim ve Klinik Bul | Randevu Ajandam',
            'meta_aciklama' => 'Türkiye genelinde uzman doktor ve kliniklerden online randevu alın. Hekim arayın, müsait saat seçin, hasta randevunuzu anında oluşturun. Diyetisyen, psikolog, diş hekimi ve tüm branşlar — Randevu Ajandam.',
            'meta_anahtar_kelimeler' => 'online randevu, doktor randevu, hekim randevu, klinik randevu, hasta randevu, uzman doktor bul, diyetisyen randevu, psikolog randevu, diş hekimi randevu, randevu ajandam, randevu yazılımı',
            'meta_yazar' => 'Randevu Ajandam',
        ]);
    }
}
