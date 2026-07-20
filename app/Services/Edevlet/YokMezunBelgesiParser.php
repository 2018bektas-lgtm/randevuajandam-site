<?php

namespace App\Services\Edevlet;

/**
 * YÖK e-Devlet mezun belgesi PDF / düz metin parse.
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
        $norm = $this->normalizeLines($text);

        $barkod = $this->match($norm, '/\b(YOKME[A-Z0-9]{8,})\b/i')
            ?? $this->match($norm, '/\b([A-Z]{3,}[A-Z0-9]{10,})\b/');

        $tc = $this->fieldAfter($norm, ['T.C. Kimlik No', 'T.C. Kimlik No:', 'TC Kimlik No']);
        if (! $tc) {
            $tc = $this->match($norm, '/\b([1-9][0-9]{10})\b/');
        }
        $tc = $tc ? preg_replace('/\D/', '', $tc) : null;

        $ad = $this->fieldAfter($norm, ['Adı Soyadı', 'Adi Soyadi', 'Ad Soyad']);
        $program = $this->fieldAfter($norm, ['Program']);
        $diplomaNo = $this->fieldAfter($norm, ['Diploma No']);
        $diplomaNotu = $this->fieldAfter($norm, ['Diploma Notu']);
        $mezuniyet = $this->fieldAfter($norm, ['Mezuniyet Tarihi', 'Mezuniyet Tarihi :']);
        $durum = $this->fieldAfter($norm, ['Durum']);

        $split = $this->splitProgram($program);

        return [
            'barkod' => $barkod ? strtoupper($barkod) : null,
            'tc' => $tc,
            'ad_soyad' => $ad,
            'baba_adi' => $this->fieldAfter($norm, ['Baba Adı', 'Baba Adi']),
            'anne_adi' => $this->fieldAfter($norm, ['Anne Adı', 'Anne Adi']),
            'dogum_tarihi' => $this->fieldAfter($norm, ['Doğum Tarihi', 'Dogum Tarihi']),
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
     * PDF dosyasından metin (smalot/pdfparser varsa).
     *
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

    protected function extractPdfText(string $absolutePath): string
    {
        // 1) smalot/pdfparser
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $parser = new \Smalot\PdfParser\Parser;
                $pdf = $parser->parseFile($absolutePath);
                $t = trim((string) $pdf->getText());
                if ($t !== '') {
                    return $t;
                }
            } catch (\Throwable) {
                // fall through
            }
        }

        // 2) pdftotext (poppler)
        $bin = PHP_OS_FAMILY === 'Windows' ? 'pdftotext.exe' : 'pdftotext';
        $out = $absolutePath.'.txt';
        @exec(escapeshellcmd($bin).' -layout '.escapeshellarg($absolutePath).' '.escapeshellarg($out).' 2>&1');
        if (is_file($out)) {
            $t = trim((string) file_get_contents($out));
            @unlink($out);
            if ($t !== '') {
                return $t;
            }
        }

        // 3) Ham binary: UTF-16BE/LE ve Latin metin parçaları (YÖK PDF)
        $raw = (string) file_get_contents($absolutePath);
        $chunks = [];
        // UTF-16BE pairs often used in Turkish PDFs
        if (preg_match_all('/(?:\x00[\x20-\x7E]){4,}/', $raw, $m)) {
            foreach ($m[0] as $seq) {
                $chunks[] = preg_replace('/\x00/', '', $seq) ?? '';
            }
        }
        if (preg_match_all('/(?:[\x20-\x7E]\x00){4,}/', $raw, $m)) {
            foreach ($m[0] as $seq) {
                $chunks[] = preg_replace('/\x00/', '', $seq) ?? '';
            }
        }
        if (preg_match_all('/[\x20-\x7E\xC0-\xFF]{5,}/u', $raw, $m)) {
            $chunks = array_merge($chunks, $m[0]);
        }

        return trim(implode("\n", array_unique(array_filter($chunks))));
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
            // Label : value aynı satır veya sonraki satır
            if (preg_match('/'.$q.'\s*:?\s*([^\n]+)/iu', $text, $m)) {
                $v = trim($m[1]);
                $v = trim($v, " \t:-");
                if ($v !== '' && mb_strlen($v) < 400) {
                    return $v;
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
        $parts = array_values(array_filter(array_map('trim', explode('/', $program))));

        return [
            'universite' => $parts[0] ?? null,
            'fakulte' => $parts[1] ?? null,
            'bolum' => $parts[2] ?? ($parts[1] ?? null),
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

        return null;
    }
}
