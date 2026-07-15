<?php

namespace Database\Seeders;

use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Randevu;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HastaVeRandevuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create/Update Patients
        $hasta1 = Hasta::updateOrCreate(
            ['e_posta' => 'ayse@test.com'],
            [
                'ad' => 'Ayşe',
                'soyad' => 'Yılmaz',
                'sifre' => Hash::make('sifre123'),
                'telefon' => '05551112233',
                'aktif_mi' => true,
            ]
        );

        $hasta2 = Hasta::updateOrCreate(
            ['e_posta' => 'mehmet@test.com'],
            [
                'ad' => 'Mehmet',
                'soyad' => 'Can',
                'sifre' => Hash::make('sifre123'),
                'telefon' => '05552223344',
                'aktif_mi' => true,
            ]
        );

        $hasta3 = Hasta::updateOrCreate(
            ['e_posta' => 'zeynep@test.com'],
            [
                'ad' => 'Zeynep',
                'soyad' => 'Kaya',
                'sifre' => Hash::make('sifre123'),
                'telefon' => '05553334455',
                'aktif_mi' => true,
            ]
        );

        $hasta4 = Hasta::updateOrCreate(
            ['e_posta' => 'ali@test.com'],
            [
                'ad' => 'Ali',
                'soyad' => 'Demir',
                'sifre' => Hash::make('sifre123'),
                'telefon' => '05554445566',
                'aktif_mi' => true,
            ]
        );

        // Clean up previously seeded appointments to prevent duplicate accumulation
        Randevu::whereIn('e_posta', ['ayse@test.com', 'mehmet@test.com', 'zeynep@test.com', 'ali@test.com'])->delete();

        // 2. Fetch Doctors and their Services to create appointments
        $doktorlar = Doktor::all();

        foreach ($doktorlar as $doktor) {
            $hizmetler = $doktor->hizmetler;
            if ($hizmetler->isEmpty()) {
                continue;
            }

            $hizmet1 = $hizmetler->first();
            $hizmet2 = $hizmetler->count() > 1 ? $hizmetler->skip(1)->first() : $hizmet1;

            // Target Date (2026-06-11)
            $targetDate = Carbon::parse('2026-06-11');

            // past appointment: today 10:00 (Kardiyoloji Muayenesi - 30 dk)
            Randevu::create([
                'doktor_id' => $doktor->id,
                'hizmet_id' => $hizmet1->id,
                'hasta_id' => $hasta1->id,
                'ad' => $hasta1->ad,
                'soyad' => $hasta1->soyad,
                'telefon' => $hasta1->telefon,
                'e_posta' => $hasta1->e_posta,
                'tarih' => $targetDate->toDateString(),
                'saat' => '10:00',
                'not' => 'Rutin kontrol randevusu.',
                'durum' => 'tamamlandi',
                'hekim_notu' => 'Hastanın genel kontrolleri tamamlandı, durum stabil.',
            ]);

            // future appointment today: today 14:30
            Randevu::create([
                'doktor_id' => $doktor->id,
                'hizmet_id' => $hizmet2->id,
                'hasta_id' => $hasta2->id,
                'ad' => $hasta2->ad,
                'soyad' => $hasta2->soyad,
                'telefon' => $hasta2->telefon,
                'e_posta' => $hasta2->e_posta,
                'tarih' => $targetDate->toDateString(),
                'saat' => '14:30',
                'not' => 'Hizmet detayları hakkında görüşmek istiyorum.',
                'durum' => 'onaylandi',
            ]);

            // tomorrow's appointment: tomorrow 09:30
            Randevu::create([
                'doktor_id' => $doktor->id,
                'hizmet_id' => $hizmet1->id,
                'hasta_id' => $hasta3->id,
                'ad' => $hasta3->ad,
                'soyad' => $hasta3->soyad,
                'telefon' => $hasta3->telefon,
                'e_posta' => $hasta3->e_posta,
                'tarih' => $targetDate->copy()->addDay()->toDateString(),
                'saat' => '09:30',
                'not' => 'İlk kontrol seansım olacak.',
                'durum' => 'onaylandi',
            ]);

            // pending request next week: Monday 11:00
            Randevu::create([
                'doktor_id' => $doktor->id,
                'hizmet_id' => $hizmet2->id,
                'hasta_id' => $hasta4->id,
                'ad' => $hasta4->ad,
                'soyad' => $hasta4->soyad,
                'telefon' => $hasta4->telefon,
                'e_posta' => $hasta4->e_posta,
                'tarih' => $targetDate->copy()->addDays(4)->toDateString(), // 2026-06-15 Pazartesi
                'saat' => '11:00',
                'not' => 'Seçtiğim saatler uygunsa onayınızı bekliyorum.',
                'durum' => 'beklemede',
            ]);
        }
    }
}
