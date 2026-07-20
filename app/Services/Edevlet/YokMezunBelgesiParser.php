<?php

namespace App\Services\Edevlet;

/**
 * YÖK e-Devlet mezun belgesi PDF / düz metin parse.
 * Türkçe karakter (Windows-1254 / mojibake) düzeltmesi içerir.
 */
class YokMezunBelgesiParser
{
    /**
     * @return array{
     *   barkod:?string, tc:?string, ad_soyad:?string, baba_adi:?string, anne_adi:?string,
     *   dogum_tarihi:?string, program:?string, universite:?string, fakulte:?string, bolum:?string,
     *   diploma_no:?string, diploma_notu:?string, mezuniyet_tarihi:?string, durum:?string, ham:string
     * }
     */
    public function parseText(string $text): array
    {
        $norm = $this->normalizeLines($this->fixTurkishEncoding($text));

        $barkod = $this->match($norm, '/\b(YOKME[A-Z0-9]{8,})\b/i')
            ?? $this->match($norm, '/\b([A-Z]{3,}[A-Z0-9]{10,})\b/');

        $tc = $this->fieldAfter($norm, [
            'T.C. Kimlik No', 'T.C. Kimlik No:', 'TC Kimlik No',
            'T.C Kimlik No', 'TC. Kimlik No',
        ]);
        if (! $tc) {
            $tc = $this->match($norm, '/\b([1-9][0-9]{10})\b/');
        }
        $tc = $tc ? preg_replace('/\D/', '', $tc) : null;
        if ($tc && strlen($tc) !== 11) {
            $tc = null;
        }

        $ad = $this->fieldAfter($norm, [
            'Adı Soyadı', 'Adi Soyadi', 'Ad Soyad', 'Adı Soyadı:',
            'ADI SOYADI', 'Ad Soyadı',
        ]);
        $program = $this->fieldAfter($norm, ['Program', 'PROGRAM']);
        $diplomaNo = $this->fieldAfter($norm, ['Diploma No', 'DIPLOMA NO']);
        $diplomaNotu = $this->fieldAfter($norm, ['Diploma Notu', 'DIPLOMA NOTU']);
        $mezuniyet = $this->fieldAfter($norm, [
            'Mezuniyet Tarihi', 'Mezuniyet Tarihi :', 'MEZUNIYET TARIHI', 'Mezuniyet Tarihi:',
        ]);
        $durum = $this->fieldAfter($norm, ['Durum', 'DURUM']);

        // Program satırı bazen bozulmuş etiketle gelebilir; / içeren uzun satırı yakala
        if (! $program || ! str_contains($program, '/')) {
            if (preg_match('/([A-ZÇĞİÖŞÜÝÐÞ\s\.]+ÜN[İIÝ]VERS[İIÝ]TES[İIÝ]\s*\/[^\n]{5,200})/iu', $norm, $m)) {
                $program = $this->fixTurkishEncoding(trim($m[1]));
            }
        }

        $program = $program ? $this->fixTurkishEncoding($program) : null;
        $ad = $ad ? $this->fixTurkishEncoding($ad) : null;

        $split = $this->splitProgram($program);

        return [
            'barkod' => $barkod ? strtoupper($barkod) : null,
            'tc' => $tc,
            'ad_soyad' => $ad,
            'baba_adi' => $this->fieldAfter($norm, ['Baba Adı', 'Baba Adi', 'BABA ADI']),
            'anne_adi' => $this->fieldAfter($norm, ['Anne Adı', 'Anne Adi', 'ANNE ADI']),
            'dogum_tarihi' => $this->fieldAfter($norm, ['Doğum Tarihi', 'Dogum Tarihi', 'DOGUM TARIHI']),
            'program' => $program,
            'universite' => $split['universite'],
            'fakulte' => $split['fakulte'],
            'bolum' => $split['bolum'],
            'diploma_no' => $diplomaNo,
            'diploma_notu' => $diplomaNotu,
            'mezuniyet_tarihi' => $this->normalizeDate($mezuniyet),
            'durum' => $durum,
            'ham' => mb_substr($norm, 0, 8000),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function parsePdfFile(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new \InvalidArgumentException('PDF dosyası bulunamadı.');
        }

        $text = $this->extractPdfText($absolutePath);

        if (trim($text) === '') {
            throw new \RuntimeException('PDF metni okunamadı. Barkod numarasını elle girin.');
        }

        return $this->parseText($text);
    }

    /**
     * PDF / Windows-1254 / mojibake Türkçe karakter düzeltmesi.
     * Örn: ÜNÝVERSÝTESÝ → ÜNİVERSİTESİ, PSÝKOLOJÝ → PSİKOLOJİ
     */
    public function fixTurkishEncoding(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        // 1) Windows-1254 (Türkçe) olarak yorumlanabilecek bozulmuş UTF-8
        if (preg_match('/[ÝýÞþÐð]/u', $text) || preg_match('/Ã.|Â./u', $text)) {
            // Klasik CP1252/Latin1 mojibake: Ý = İ (0xDD)
            $map = [
                'Ý' => 'İ', 'ý' => 'ı',
                'Þ' => 'Ş', 'þ' => 'ş',
                'Ð' => 'Ğ', 'ð' => 'ğ',
                'Ø' => 'Ö', // nadir
            ];
            $text = strtr($text, $map);

            // UTF-8 double-encoding kalıntıları
            $utf8Fixes = [
                'Ã¼' => 'ü', 'Ãœ' => 'Ü', 'Ãœ' => 'Ü',
                'Ã¶' => 'ö', 'Ã–' => 'Ö',
                'Ã§' => 'ç', 'Ã‡' => 'Ç',
                'Ä±' => 'ı', 'Ä°' => 'İ',
                'ÄŸ' => 'ğ', 'Äž' => 'Ğ',
                'ÅŸ' => 'ş', 'Åž' => 'Ş',
                'ÃœNÄ°VERSÄ°TE' => 'ÜNİVERSİTE',
            ];
            $text = str_replace(array_keys($utf8Fixes), array_values($utf8Fixes), $text);
        }

        // 2) Ham byte dizisi Windows-1254 ise
        if (! mb_check_encoding($text, 'UTF-8')) {
            $converted = @mb_convert_encoding($text, 'UTF-8', 'Windows-1254');
            if (is_string($converted) && $converted !== '') {
                $text = $converted;
            } else {
                $converted = @iconv('Windows-1254', 'UTF-8//IGNORE', $text);
                if (is_string($converted) && $converted !== '') {
                    $text = $converted;
                }
            }
        }

        // 3) Hâlâ Ý varsa tekrar map
        if (str_contains($text, 'Ý') || str_contains($text, 'ý')) {
            $text = strtr($text, ['Ý' => 'İ', 'ý' => 'ı', 'Þ' => 'Ş', 'þ' => 'ş', 'Ð' => 'Ğ', 'ð' => 'ğ']);
        }

        // 4) Bilinen kelime düzeltmeleri (YÖK PDF)
        $wordFixes = [
            'ÜNÝVERSÝTESÝ' => 'ÜNİVERSİTESİ',
            'ÜNIVERSITESI' => 'ÜNİVERSİTESİ',
            'UNIVERSITESI' => 'ÜNİVERSİTESİ',
            'FAKÜLTESÝ' => 'FAKÜLTESİ',
            'FAKULTESI' => 'FAKÜLTESİ',
            'PSÝKOLOJÝ' => 'PSİKOLOJİ',
            'PSIKOLOJI' => 'PSİKOLOJİ',
            'EDEBÝYAT' => 'EDEBİYAT',
            'EDEBIYAT' => 'EDEBİYAT',
            'TÝP' => 'TIP',
            'DÝŞ' => 'DİŞ',
            'DÝS' => 'DİŞ',
            'HEKÝMLÝĞÝ' => 'HEKİMLİĞİ',
            'HEKIMLIGI' => 'HEKİMLİĞİ',
        ];
        $text = str_ireplace(array_keys($wordFixes), array_values($wordFixes), $text);

        return $text;
    }

    protected function extractPdfText(string $absolutePath): string
    {
        $candidates = [];

        // 1) smalot/pdfparser
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $parser = new \Smalot\PdfParser\Parser;
                $pdf = $parser->parseFile($absolutePath);
                $t = trim((string) $pdf->getText());
                if ($t !== '') {
                    $candidates[] = $this->fixTurkishEncoding($t);
                }
            } catch (\Throwable) {
                // fall through
            }
        }

        // 2) pdftotext (poppler) — genelde en iyi Türkçe
        $bin = PHP_OS_FAMILY === 'Windows' ? 'pdftotext' : 'pdftotext';
        $out = $absolutePath.'.txt';
        @exec(escapeshellarg($bin).' -enc UTF-8 -layout '.escapeshellarg($absolutePath).' '.escapeshellarg($out).' 2>&1');
        if (is_file($out)) {
            $t = trim((string) file_get_contents($out));
            @unlink($out);
            if ($t !== '') {
                $candidates[] = $this->fixTurkishEncoding($t);
            }
        }

        // 3) UTF-16 metin akışları (YÖK PDF)
        $raw = (string) file_get_contents($absolutePath);
        $chunks = [];
        if (preg_match_all('/(?:\x00[\x09\x0A\x0D\x20-\x7E\xC0-\xFF]){4,}/', $raw, $m)) {
            foreach ($m[0] as $seq) {
                $decoded = @mb_convert_encoding($seq, 'UTF-8', 'UTF-16BE');
                if (is_string($decoded) && preg_match('/[A-Za-zÇĞİÖŞÜçğıöşü]/u', $decoded)) {
                    $chunks[] = $decoded;
                } else {
                    $chunks[] = preg_replace('/\x00/', '', $seq) ?? '';
                }
            }
        }
        if (preg_match_all('/(?:[\x09\x0A\x0D\x20-\x7E]\x00){4,}/', $raw, $m)) {
            foreach ($m[0] as $seq) {
                $decoded = @mb_convert_encoding($seq, 'UTF-8', 'UTF-16LE');
                if (is_string($decoded) && preg_match('/[A-Za-zÇĞİÖŞÜçğıöşü]/u', $decoded)) {
                    $chunks[] = $decoded;
                }
            }
        }
        // Windows-1254 tek bayt Türkçe
        if (preg_match_all('/[\x20-\x7E\x80-\xFF]{6,}/', $raw, $m)) {
            foreach ($m[0] as $seq) {
                $as1254 = @mb_convert_encoding($seq, 'UTF-8', 'Windows-1254');
                if (is_string($as1254) && preg_match('/(ÜNİVERSİTE|PSİKOLOJİ|Kimlik|Diploma|Program|Mezun)/iu', $as1254)) {
                    $chunks[] = $as1254;
                }
            }
        }
        if ($chunks !== []) {
            $candidates[] = $this->fixTurkishEncoding(implode("\n", array_unique(array_filter($chunks))));
        }

        // En iyi adayı seç: Türkçe anahtar kelime skoru
        $best = '';
        $bestScore = -1;
        foreach ($candidates as $c) {
            $score = 0;
            foreach (['Kimlik', 'Adı', 'Program', 'Diploma', 'ÜNİVERSİTE', 'Mezun', 'YOKME'] as $kw) {
                if (stripos($c, $kw) !== false) {
                    $score += 2;
                }
            }
            // Bozuk Ý cezası
            if (str_contains($c, 'Ý') || str_contains($c, 'ý')) {
                $score -= 3;
            }
            if ($score > $bestScore || ($score === $bestScore && mb_strlen($c) > mb_strlen($best))) {
                $bestScore = $score;
                $best = $c;
            }
        }

        return trim($best);
    }

    protected function normalizeLines(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    protected function match(string $text, string $pattern): ?string
    {
        if (preg_match($pattern, $text, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /**
     * @param  array<int, string>  $labels
     */
    protected function fieldAfter(string $text, array $labels): ?string
    {
        foreach ($labels as $label) {
            $q = preg_quote($label, '/');
            // Aynı satır veya sonraki satır
            if (preg_match('/'.$q.'\s*:?\s*\n?\s*([^\n]+)/iu', $text, $m)) {
                $v = trim($m[1]);
                $v = trim($v, " \t:-");
                // Etiket tekrarı değilse
                if ($v !== '' && mb_strlen($v) < 400 && ! preg_match('/^(T\.C\.|Adı|Program|Diploma|Durum)/iu', $v)) {
                    return $this->fixTurkishEncoding($v);
                }
            }
        }

        return null;
    }

    /**
     * @return array{universite:?string,fakulte:?string,bolum:?string}
     */
    protected function splitProgram(?string $program): array
    {
        if (! $program) {
            return ['universite' => null, 'fakulte' => null, 'bolum' => null];
        }
        $program = $this->fixTurkishEncoding($program);
        $parts = array_values(array_filter(array_map('trim', explode('/', $program))));

        return [
            'universite' => isset($parts[0]) ? $this->fixTurkishEncoding($parts[0]) : null,
            'fakulte' => isset($parts[1]) ? $this->fixTurkishEncoding($parts[1]) : null,
            'bolum' => isset($parts[2])
                ? $this->fixTurkishEncoding($parts[2])
                : (isset($parts[1]) ? $this->fixTurkishEncoding($parts[1]) : null),
        ];
    }

    protected function normalizeDate(?string $d): ?string
    {
        if (! $d) {
            return null;
        }
        $d = trim($d);
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $d, $m)) {
            return $m[3].'-'.$m[2].'-'.$m[1];
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d)) {
            return $d;
        }

        return null;
    }
}
