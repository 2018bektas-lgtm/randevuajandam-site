<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Doktor;
use App\Models\Paket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalDevDoktorSeeder extends Seeder
{
    // Hekim .env'e yazılacak sabit değerler
    const API_KEY    = 'ra-hekim-dev-local';
    const API_SECRET = 'hekim-dev-secret-local';

    public function run(): void
    {
        // web_sitesi özelliğine sahip paketi bul
        $paket = Paket::whereHas('sistemOzellikleri', fn ($q) => $q->where('kod', 'web_sitesi'))
            ->where('tur', 'bireysel')
            ->first();

        if (! $paket) {
            $this->command->error('web_sitesi özelliğine sahip paket bulunamadı. Önce db:seed çalıştırın.');
            return;
        }

        // Test doktoru oluştur veya güncelle
        $doktor = Doktor::updateOrCreate(
            ['e_posta' => 'hekim@site.local'],
            [
                'ad_soyad'          => 'Test Hekim',
                'unvan'             => 'Uzm. Dr.',
                'uzmanlik_alani'    => 'Psikoloji',
                'sifre'             => Hash::make('hekim123'),
                'telefon'           => '05551234567',
                'aktif_mi'          => true,
                'platformda_gorunur'=> false,
                'paket_id'          => $paket->id,
                'uyelik_baslangic'  => now(),
                'uyelik_bitis'      => now()->addYears(10),
            ]
        );

        // API key oluştur (sabit key + secret)
        ApiKey::query()->where('doktor_id', $doktor->id)->delete();
        ApiKey::query()->create([
            'doktor_id'  => $doktor->id,
            'api_key'    => self::API_KEY,
            'secret_key' => ApiKey::hashSecret(self::API_SECRET),
            'durum'      => true,
        ]);

        $this->command->info('');
        $this->command->info('✓ Test doktor oluşturuldu: hekim@site.local / hekim123');
        $this->command->info('✓ Paket: '.$paket->ad);
        $this->command->info('');
        $this->command->info('Hekim .env için:');
        $this->command->line('  RANDEVU_API_KEY='.self::API_KEY);
        $this->command->line('  RANDEVU_API_SECRET='.self::API_SECRET);
        $this->command->info('');
    }
}
