<?php

namespace Tests\Unit;

use App\Services\ProfanityFilter;
use PHPUnit\Framework\TestCase;

class ProfanityFilterTest extends TestCase
{
    public function test_detects_turkish_profanity(): void
    {
        $filter = new ProfanityFilter(['siktir', 'amk', 'salak']);

        $this->assertTrue($filter->contains('Bu doktor çok salak'));
        $this->assertTrue($filter->contains('AMK ne iş'));
        $this->assertFalse($filter->contains('Çok ilgili ve nazik bir hekim.'));
    }

    public function test_ignores_clean_medical_text(): void
    {
        $filter = new ProfanityFilter(['siktir', 'orospu', 'fuck']);

        $this->assertFalse($filter->contains('Muayene süresi 20 dakikaydı, memnun kaldım.'));
        $this->assertFalse($filter->contains('İlaç dozajı ayarlandı, teşekkürler.'));
    }
}
