<?php

namespace Tests\Unit;

use App\Services\Edevlet\YokMezunBelgesiParser;
use App\Services\Meslek\MeslekEslesmeService;
use PHPUnit\Framework\TestCase;

class YokMezunBelgesiParserTest extends TestCase
{
    public function test_parses_yok_mezun_text(): void
    {
        $sample = <<<TXT
YOKME50DTYWEU0P9XV
MEZUN BELGESİ
T.C. Kimlik No
: 18307868810
Adı Soyadı
: AYŞE GÜVEN
Program
: HACETTEPE ÜNİVERSİTESİ/EDEBİYAT FAKÜLTESİ/PSİKOLOJİ
Diploma No
: 95-044-009
Diploma Notu
: 3.4 / 4
Mezuniyet Tarihi : 19.06.1995
Durum
: MEZUNİYET
TXT;

        $parser = new YokMezunBelgesiParser;
        $p = $parser->parseText($sample);

        $this->assertSame('YOKME50DTYWEU0P9XV', $p['barkod']);
        $this->assertSame('18307868810', $p['tc']);
        $this->assertSame('AYŞE GÜVEN', $p['ad_soyad']);
        $this->assertStringContainsString('PSİKOLOJİ', (string) $p['program']);
        $this->assertSame('95-044-009', $p['diploma_no']);
        $this->assertSame('1995-06-19', $p['mezuniyet_tarihi']);
        $this->assertSame('HACETTEPE ÜNİVERSİTESİ', $p['universite']);
    }

    public function test_name_similarity(): void
    {
        $s = new MeslekEslesmeService;
        $this->assertGreaterThanOrEqual(0.85, $s->adBenzerlik('Ayşe Güven', 'AYŞE GÜVEN'));
        $this->assertLessThan(0.5, $s->adBenzerlik('Ayşe Güven', 'Mehmet Yılmaz'));
    }
}
