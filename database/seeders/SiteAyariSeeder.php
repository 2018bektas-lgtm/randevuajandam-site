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
            'meta_baslik' => 'Randevu Ajandam - Premium Randevu ve Danışan Yönetim Platformu',
            'meta_aciklama' => 'Randevu Ajandam, uzmanlar ile danışanları en hızlı ve prestijli şekilde buluşturan, ajanda ve randevu süreçlerini mükemmelleştiren modern bir SaaS platformudur.',
            'meta_anahtar_kelimeler' => 'randevu, online randevu, ajanda yönetimi, doktor randevu, diyetisyen randevu, psikolog randevu, klinik yönetim sistemi, randevu takvimi',
            'meta_yazar' => 'Randevu Ajandam',
        ]);
    }
}
