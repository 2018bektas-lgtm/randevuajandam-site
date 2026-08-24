<?php

namespace App\Console\Commands;

use App\Models\Doktor;
use App\Models\Klinik;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Doktor ve klinik slug'larini yeni kurala gore yeniden uretir.
 * Doktor: sadece ad_soyad (unvan artik slug'a girmez).
 * Klinik: mevcut mantik (yalnizca ad); eski_slug korumasi eklenir.
 * Slug degisirse eski deger eski_slug alanina alinir → 301 redirect ile linkler yasar.
 */
class SlugYenidenUretCommand extends Command
{
    protected $signature = 'ra:slug-yeniden-uret
        {--dry : Yalnizca ne degisecekleri goster, kaydetme}
        {--only= : Sadece belirli bir tipi guncelle (doktor | klinik)}';

    protected $description = 'Doktor + klinik slug degerlerini yeni kurala gore yeniden uretir (eski_slug korunur).';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $only = (string) ($this->option('only') ?? '');
        $only = in_array($only, ['doktor', 'klinik'], true) ? $only : '';

        if ($dry) {
            $this->warn('DRY-RUN: hiçbir değişiklik kaydedilmiyor');
        }

        if ($only === '' || $only === 'doktor') {
            $this->doktorlariGuncelle($dry);
        }

        if ($only === '' || $only === 'klinik') {
            $this->klinikleriGuncelle($dry);
        }

        return self::SUCCESS;
    }

    protected function doktorlariGuncelle(bool $dry): void
    {
        $this->line('');
        $this->info('Doktorlar taraniyor...');
        $sayac = ['toplam' => 0, 'degisti' => 0, 'ayni' => 0];

        Doktor::query()
            ->orderBy('id')
            ->chunkById(200, function ($doktorlar) use ($dry, &$sayac) {
                foreach ($doktorlar as $doktor) {
                    $sayac['toplam']++;
                    $yeniSlug = $this->benzersizDoktorSlug($doktor);
                    $mevcut = (string) $doktor->slug;

                    if ($yeniSlug === $mevcut) {
                        $sayac['ayni']++;
                        continue;
                    }

                    $this->line(sprintf('  #%d  %s  →  %s', $doktor->id, $mevcut ?: '(bos)', $yeniSlug));
                    $sayac['degisti']++;

                    if (! $dry) {
                        DB::table('doktorlar')
                            ->where('id', $doktor->id)
                            ->update([
                                'slug' => $yeniSlug,
                                'eski_slug' => $mevcut !== '' ? $mevcut : null,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

        $this->newLine();
        $this->info(sprintf('Doktor ozet: toplam=%d, degisti=%d, ayni=%d', $sayac['toplam'], $sayac['degisti'], $sayac['ayni']));
    }

    protected function klinikleriGuncelle(bool $dry): void
    {
        $this->line('');
        $this->info('Klinikler taraniyor...');
        $sayac = ['toplam' => 0, 'degisti' => 0, 'ayni' => 0];

        Klinik::query()
            ->orderBy('id')
            ->chunkById(200, function ($klinikler) use ($dry, &$sayac) {
                foreach ($klinikler as $klinik) {
                    $sayac['toplam']++;
                    $yeniSlug = Klinik::generateUniqueSlug((string) $klinik->ad, $klinik->id);
                    $mevcut = (string) $klinik->slug;

                    if ($yeniSlug === $mevcut) {
                        $sayac['ayni']++;
                        continue;
                    }

                    $this->line(sprintf('  #%d  %s  →  %s', $klinik->id, $mevcut ?: '(bos)', $yeniSlug));
                    $sayac['degisti']++;

                    if (! $dry) {
                        DB::table('klinikler')
                            ->where('id', $klinik->id)
                            ->update([
                                'slug' => $yeniSlug,
                                'eski_slug' => $mevcut !== '' ? $mevcut : null,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

        $this->newLine();
        $this->info(sprintf('Klinik ozet: toplam=%d, degisti=%d, ayni=%d', $sayac['toplam'], $sayac['degisti'], $sayac['ayni']));
    }

    protected function benzersizDoktorSlug(Doktor $doktor): string
    {
        $baseSlug = Str::slug((string) $doktor->ad_soyad) ?: 'hekim';
        $slug = $baseSlug;
        $counter = 1;

        while (Doktor::where('il_id', $doktor->il_id)
            ->where('ilce_id', $doktor->ilce_id)
            ->where('slug', $slug)
            ->where('id', '!=', $doktor->id)
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
