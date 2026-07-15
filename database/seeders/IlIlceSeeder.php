<?php

namespace Database\Seeders;

use App\Models\Il;
use App\Models\Ilce;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class IlIlceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = public_path('assets/data/cities.json');
        if (! File::exists($jsonPath)) {
            return;
        }

        // Clean tables to prevent duplicates
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Ilce::truncate();
        Il::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $json = File::get($jsonPath);
        $cities = json_decode($json, true);

        if (empty($cities)) {
            return;
        }

        foreach ($cities as $cityData) {
            $ilName = $this->capitalizeTurkish($cityData['name']);

            $il = Il::create([
                'ad' => $ilName,
                'plaka' => $cityData['plate'],
            ]);

            if (isset($cityData['counties']) && is_array($cityData['counties'])) {
                foreach ($cityData['counties'] as $countyName) {
                    $ilceName = $this->capitalizeTurkish($countyName);

                    Ilce::create([
                        'il_id' => $il->id,
                        'ad' => $ilceName,
                    ]);
                }
            }
        }
    }

    /**
     * Capitalize Turkish characters correctly.
     */
    private function capitalizeTurkish(string $str): string
    {
        $words = explode(' ', $str);
        $capitalizedWords = array_map(function ($word) {
            if (empty($word)) {
                return '';
            }
            $firstChar = mb_substr($word, 0, 1, 'UTF-8');
            $firstChar = mb_strtoupper(
                str_replace(['i', 'ı', 'ş', 'ğ', 'ü', 'ö', 'ç'], ['İ', 'I', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'], $firstChar),
                'UTF-8'
            );

            $rest = mb_substr($word, 1, null, 'UTF-8');
            $rest = mb_strtolower(
                str_replace(['İ', 'I', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'], ['i', 'ı', 'ş', 'ğ', 'ü', 'ö', 'ç'], $rest),
                'UTF-8'
            );

            return $firstChar.$rest;
        }, $words);

        return implode(' ', $capitalizedWords);
    }
}
