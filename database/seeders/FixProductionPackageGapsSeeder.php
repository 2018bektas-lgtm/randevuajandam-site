<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\PaketOzelligi;
use Illuminate\Database\Seeder;

/**
 * Canlı paket flag / domain_dahil / deneme_gun tutarlılığı.
 * Fiyat ve limitler için PaketSeeder + KlinikSeeder çalıştırın.
 */
class FixProductionPackageGapsSeeder extends Seeder
{
    public function run(): void
    {
        Paket::query()
            ->where('tur', 'bireysel')
            ->where(function ($q) {
                $q->where('ad', 'like', '%Başlangıç%')
                    ->orWhere('ad', 'like', '%Starter%');
            })
            ->update(['deneme_gun' => 14]);

        $webKod = PaketOzelligi::query()->where('kod', 'web_sitesi')->first();
        $klinikWebKod = PaketOzelligi::query()->where('kod', 'klinik_web_sitesi')->first();

        $hekimWeb = Paket::query()
            ->where('tur', 'bireysel')
            ->where(function ($q) {
                $q->where('ad', 'like', '%Web Sitesi%')
                    ->orWhereHas('sistemOzellikleri', fn ($s) => $s->where('kod', 'web_sitesi'));
            })
            ->orderByDesc('aylik_fiyat')
            ->first();

        if ($hekimWeb) {
            $hekimWeb->forceFill([
                'domain_dahil_mi' => true,
                'domain_dahil_yil' => 1,
                'domain_dahil_tlds' => ['com', 'net'],
            ])->save();
            if ($webKod) {
                $ids = $hekimWeb->sistemOzellikleri()->pluck('paket_ozellikleri.id')->all();
                if (! in_array($webKod->id, $ids, true)) {
                    $ids[] = $webKod->id;
                }
                $hekimWeb->sistemOzellikleri()->sync($ids);
            }
        }

        $kBas = Paket::query()->where('tur', 'klinik')->where('ad', 'like', '%Başlangıç%')->first();
        if ($kBas) {
            $kBas->forceFill([
                'merkezi_finans_mi' => false,
                'toplu_randevu_mi' => false,
                'raporlama_mi' => false,
                'hasta_havuzu_mi' => true,
                'max_doktor_sayisi' => $kBas->max_doktor_sayisi ?: 3,
                'max_personel_sayisi' => $kBas->max_personel_sayisi ?: 1,
                'domain_dahil_mi' => false,
            ])->save();
            // Hekim paneli özellikleri KlinikSeeder ile gelir; burada silinmez.
        }

        $kPlus = Paket::query()->where('tur', 'klinik')->where('ad', 'like', '%Plus%')->first();
        if ($kPlus) {
            $kPlus->forceFill([
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => (bool) ($kPlus->raporlama_mi ?? false),
                'hasta_havuzu_mi' => true,
                'max_doktor_sayisi' => $kPlus->max_doktor_sayisi ?: 6,
                'max_personel_sayisi' => $kPlus->max_personel_sayisi ?: 2,
                'domain_dahil_mi' => false,
            ])->save();
        }

        $kPro = Paket::query()
            ->where('tur', 'klinik')
            ->where('ad', 'like', '%Profesyonel%')
            ->where('ad', 'not like', '%Plus%')
            ->first();
        if ($kPro) {
            $kPro->forceFill([
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => true,
                'hasta_havuzu_mi' => true,
                'max_doktor_sayisi' => $kPro->max_doktor_sayisi ?: 10,
                'max_personel_sayisi' => $kPro->max_personel_sayisi ?: 5,
                'domain_dahil_mi' => false,
            ])->save();
        }

        $kWeb = Paket::query()
            ->where('tur', 'klinik')
            ->where(function ($q) {
                $q->where('ad', 'like', '%Kurumsal%')
                    ->orWhere('ad', 'like', '%Web Sitesi%')
                    ->orWhere('ad', 'like', '%Özel Web%')
                    ->orWhereHas('sistemOzellikleri', fn ($s) => $s->where('kod', 'klinik_web_sitesi'));
            })
            ->orderByDesc('aylik_fiyat')
            ->first();

        if ($kWeb) {
            $kWeb->forceFill([
                'merkezi_finans_mi' => true,
                'toplu_randevu_mi' => true,
                'raporlama_mi' => true,
                'hasta_havuzu_mi' => true,
                'domain_dahil_mi' => true,
                'domain_dahil_yil' => 1,
                'domain_dahil_tlds' => ['com', 'net'],
                'max_doktor_sayisi' => $kWeb->max_doktor_sayisi ?: 999,
                'max_personel_sayisi' => $kWeb->max_personel_sayisi ?: 999,
            ])->save();
            // klinik_web_sitesi + hekim paneli özellikleri KlinikSeeder ile senkronlanır.
            if ($klinikWebKod) {
                $ids = $kWeb->sistemOzellikleri()->pluck('paket_ozellikleri.id')->all();
                if (! in_array($klinikWebKod->id, $ids, true)) {
                    $ids[] = $klinikWebKod->id;
                }
                $kWeb->sistemOzellikleri()->sync($ids);
            }
        }

        $aylik = env('IYZICO_DEFAULT_PLAN_AYLIK');
        $yillik = env('IYZICO_DEFAULT_PLAN_YILLIK');
        if ($aylik || $yillik) {
            Paket::query()
                ->where('aktif_mi', true)
                ->where(function ($q) {
                    $q->whereNull('iyzico_plan_aylik')->orWhere('iyzico_plan_aylik', '');
                })
                ->where('aylik_fiyat', '>', 0)
                ->each(function (Paket $p) use ($aylik, $yillik) {
                    $p->forceFill([
                        'iyzico_plan_aylik' => $p->iyzico_plan_aylik ?: $aylik,
                        'iyzico_plan_yillik' => $p->iyzico_plan_yillik ?: $yillik,
                    ])->save();
                });
        }
    }
}
