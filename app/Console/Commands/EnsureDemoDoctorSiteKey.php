<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Models\Doktor;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class EnsureDemoDoctorSiteKey extends Command
{
    protected $signature = 'doctor-site:ensure-key {--doktor= : Doktor ID} {--key= : Sabit api key (opsiyonel)}';

    protected $description = 'Doktor sitesi entegrasyonu için API anahtarı oluşturur veya gösterir';

    public function handle(): int
    {
        $doktorId = $this->option('doktor');
        $doktor = $doktorId
            ? Doktor::find($doktorId)
            : Doktor::query()->where('aktif_mi', true)->orderBy('id')->first();

        if (! $doktor) {
            $this->error('Aktif hekim bulunamadı.');

            return self::FAILURE;
        }

        $fixedKey = $this->option('key');
        $existing = ApiKey::where('doktor_id', $doktor->id)->first();

        if ($existing && ! $fixedKey) {
            $this->info("Hekim: {$doktor->ad_soyad} (ID {$doktor->id})");
            $this->line('API Key: '.$existing->api_key);
            $this->line('X-Api-Key header veya ?api_key= ile kullanın.');

            return self::SUCCESS;
        }

        $apiKey = $fixedKey ?: ('rk_'.strtolower(Str::random(30)));
        $secret = strtolower(Str::random(60));

        $issued = ApiKey::issue([
            'doktor_id' => $doktor->id,
            'klinik_id' => null,
            'api_key' => $apiKey,
            'durum' => true,
            'yetkiler' => ['*'],
        ], $secret);

        $this->info("Hekim: {$doktor->ad_soyad} (ID {$doktor->id})");
        $this->info('API Key oluşturuldu/güncellendi (secret hash’lendi):');
        $this->line('X-Api-Key: '.$apiKey);
        $this->line('X-Api-Secret (bir kez): '.$issued['plain_secret']);

        return self::SUCCESS;
    }
}
