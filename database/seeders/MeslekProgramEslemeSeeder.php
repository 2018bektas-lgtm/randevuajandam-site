<?php

namespace Database\Seeders;

use App\Models\MeslekProgramEsleme;
use Illuminate\Database\Seeder;

/**
 * Sağlık / yaşam bilimleri program → unvan / branş eşlemesi.
 * program_anahtar: YÖK program metninde aranır (normalize, contains).
 * oncelik yüksek + anahtar uzun olan kazanır.
 */
class MeslekProgramEslemeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Hekimlik
            ['program_anahtar' => 'TIP FAKULTESI', 'unvan_ad' => 'Dr.', 'brans_ad' => null, 'oncelik' => 200, 'auto_onay' => true],
            ['program_anahtar' => 'TIP', 'unvan_ad' => 'Dr.', 'brans_ad' => null, 'oncelik' => 150, 'auto_onay' => true],
            ['program_anahtar' => 'DIS HEKIMLIGI', 'unvan_ad' => 'Dt.', 'brans_ad' => 'Diş Hekimliği', 'oncelik' => 200, 'auto_onay' => true],
            ['program_anahtar' => 'DIS HEKIMI', 'unvan_ad' => 'Dt.', 'brans_ad' => 'Diş Hekimliği', 'oncelik' => 190, 'auto_onay' => true],

            // Psikoloji / ruh sağlığı
            ['program_anahtar' => 'PSİKOLOJİ', 'unvan_ad' => 'Psk.', 'brans_ad' => 'Psikoloji', 'oncelik' => 180, 'auto_onay' => true],
            ['program_anahtar' => 'PSIKOLOJI', 'unvan_ad' => 'Psk.', 'brans_ad' => 'Psikoloji', 'oncelik' => 180, 'auto_onay' => true],
            ['program_anahtar' => 'REHBERLIK VE PSIKOLOJIK DANISMANLIK', 'unvan_ad' => 'Pdr.', 'brans_ad' => 'Psikolojik Danışmanlık', 'oncelik' => 185, 'auto_onay' => true],

            // Beslenme / fizyo / eczacılık
            ['program_anahtar' => 'BESLENME VE DIYETETIK', 'unvan_ad' => 'Dyt.', 'brans_ad' => 'Beslenme ve Diyetetik', 'oncelik' => 180, 'auto_onay' => true],
            ['program_anahtar' => 'DIYETETIK', 'unvan_ad' => 'Dyt.', 'brans_ad' => 'Beslenme ve Diyetetik', 'oncelik' => 170, 'auto_onay' => true],
            ['program_anahtar' => 'FIZYOTERAPI', 'unvan_ad' => 'Fzt.', 'brans_ad' => 'Fizyoterapi', 'oncelik' => 180, 'auto_onay' => true],
            ['program_anahtar' => 'FIZYOTERAPI VE REHABILITASYON', 'unvan_ad' => 'Fzt.', 'brans_ad' => 'Fizyoterapi', 'oncelik' => 185, 'auto_onay' => true],
            ['program_anahtar' => 'ECZACILIK', 'unvan_ad' => 'Ecz.', 'brans_ad' => 'Eczacılık', 'oncelik' => 180, 'auto_onay' => true],

            // Hemşirelik / ebelik
            ['program_anahtar' => 'HEMSIRELIK', 'unvan_ad' => 'Hem.', 'brans_ad' => 'Hemşirelik', 'oncelik' => 170, 'auto_onay' => true],
            ['program_anahtar' => 'EBELIK', 'unvan_ad' => 'Ebe', 'brans_ad' => 'Ebelik', 'oncelik' => 170, 'auto_onay' => true],

            // Diğer sağlık
            ['program_anahtar' => 'ODYOMETRI', 'unvan_ad' => null, 'brans_ad' => 'Odyoloji', 'oncelik' => 160, 'auto_onay' => true],
            ['program_anahtar' => 'ODYOMETRI', 'unvan_ad' => null, 'brans_ad' => 'Odyoloji', 'oncelik' => 160, 'auto_onay' => true],
            ['program_anahtar' => 'ODYOLOJI', 'unvan_ad' => null, 'brans_ad' => 'Odyoloji', 'oncelik' => 160, 'auto_onay' => true],
            ['program_anahtar' => 'DIL VE KONUSMA TERAPISI', 'unvan_ad' => null, 'brans_ad' => 'Dil ve Konuşma Terapisi', 'oncelik' => 160, 'auto_onay' => true],
            ['program_anahtar' => 'ERGOTERAPI', 'unvan_ad' => null, 'brans_ad' => 'Ergoterapi', 'oncelik' => 160, 'auto_onay' => true],
            ['program_anahtar' => 'VETERINER', 'unvan_ad' => 'Vet. Hek.', 'brans_ad' => 'Veterinerlik', 'oncelik' => 170, 'auto_onay' => true],
        ];

        foreach ($rows as $row) {
            MeslekProgramEsleme::query()->updateOrCreate(
                ['program_anahtar' => $row['program_anahtar']],
                [
                    'unvan_ad' => $row['unvan_ad'],
                    'brans_ad' => $row['brans_ad'],
                    'oncelik' => $row['oncelik'],
                    'auto_onay' => $row['auto_onay'],
                    'aktif' => true,
                ]
            );
        }
    }
}
