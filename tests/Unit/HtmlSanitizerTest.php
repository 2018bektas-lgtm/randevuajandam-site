<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * HtmlSanitizer regresyon testleri.
 *
 * Not: sanitizeAttrValue() içindeki şema kontrolü bir dönem bozuk regex
 * kullanıyordu ("#" sınırlayıcısı + desen içinde kaçırılmamış "#"), bu da
 * link/görsel içeren HER içerikte "preg_match(): Unknown modifier ')'"
 * uyarısına ve dolayısıyla HTTP 500'e yol açıyordu. Aşağıdaki testler hem
 * geçerli URL'lerin korunduğunu hem tehlikeli girdilerin temizlendiğini
 * doğrular.
 */
class HtmlSanitizerTest extends TestCase
{
    /**
     * PHP uyarılarını exception'a çevir; bozuk regex sessizce geçmesin.
     */
    protected function setUp(): void
    {
        parent::setUp();

        set_error_handler(static function (int $no, string $str, string $file = '', int $line = 0): bool {
            throw new \ErrorException($str, 0, $no, $file, $line);
        });
    }

    protected function tearDown(): void
    {
        restore_error_handler();

        parent::tearDown();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function korunanUrlSaglayici(): array
    {
        return [
            'https link' => ['<a href="https://randevuajandam.com/iletisim">tikla</a>', 'href="https://randevuajandam.com/iletisim"'],
            'http link' => ['<a href="http://ornek.test">tikla</a>', 'href="http://ornek.test"'],
            'mailto' => ['<a href="mailto:info@randevuajandam.com">yaz</a>', 'href="mailto:info@randevuajandam.com"'],
            'tel' => ['<a href="tel:+905321112233">ara</a>', 'href="tel:+905321112233"'],
            'sayfa ici capa' => ['<a href="#bolum-2">bolum</a>', 'href="#bolum-2"'],
            'koke gore yol' => ['<img src="/uploads/blog/kapak.jpg" alt="kapak">', 'src="/uploads/blog/kapak.jpg"'],
            'ayni dizin' => ['<a href="./hizmetler">hizmetler</a>', 'href="./hizmetler"'],
            'ust dizin' => ['<a href="../hizmetler">hizmetler</a>', 'href="../hizmetler"'],
            'semasiz yol' => ['<img src="uploads/x.png" alt="x">', 'src="uploads/x.png"'],
        ];
    }

    #[DataProvider('korunanUrlSaglayici')]
    public function test_gecerli_url_korunur(string $girdi, string $beklenen): void
    {
        $this->assertStringContainsString($beklenen, HtmlSanitizer::clean($girdi));
    }

    public function test_link_iceren_icerik_hata_firlatmaz(): void
    {
        $html = '<p>Detay icin <a href="https://randevuajandam.com">tiklayin</a> '
            .'ve <img src="/uploads/a.jpg" alt="gorsel"> inceleyin.</p>';

        $temiz = HtmlSanitizer::clean($html);

        $this->assertStringContainsString('<a href="https://randevuajandam.com"', $temiz);
        $this->assertStringContainsString('<img src="/uploads/a.jpg"', $temiz);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function tehlikeliGirdiSaglayici(): array
    {
        return [
            'javascript: semasi' => ['<a href="javascript:alert(1)">tikla</a>', 'javascript:'],
            'vbscript: semasi' => ['<a href="vbscript:msgbox(1)">tikla</a>', 'vbscript:'],
            'data: semasi' => ['<img src="data:text/html;base64,PHNjcmlwdD4=">', 'data:'],
            'onerror olayi' => ['<img src="/a.jpg" onerror="alert(1)">', 'onerror'],
            'onmouseover olayi' => ['<a href="/x" onmouseover="alert(1)">t</a>', 'onmouseover'],
            'script etiketi' => ['<p>ok</p><script>alert(1)</script>', '<script'],
            'iframe etiketi' => ['<iframe src="https://kotu.test"></iframe>', '<iframe'],
            'style niteligi' => ['<p style="position:fixed">x</p>', 'style='],
        ];
    }

    #[DataProvider('tehlikeliGirdiSaglayici')]
    public function test_tehlikeli_girdi_temizlenir(string $girdi, string $olmamasiGereken): void
    {
        $this->assertStringNotContainsStringIgnoringCase(
            $olmamasiGereken,
            HtmlSanitizer::clean($girdi)
        );
    }


    /**
     * @return array<string, array{0: string}>
     */
    public static function bosNitelikSaglayici(): array
    {
        return [
            'bos alt' => ['<img src="/uploads/a.jpg" alt="">'],
            'bos title' => ['<a href="https://x.test" title="">t</a>'],
            'bos alt + dolu title' => ['<img src="/a.jpg" alt="" title="kapak">'],
        ];
    }

    /**
     * Regresyon: filterAttributes() eslesmeyen regex grubunu ($m[4]) dogrudan
     * okuyordu; `alt=""` gibi BOS bir nitelik "Undefined array key 4" ->
     * ErrorException -> HTTP 500 uretiyordu. CKEditor ciktisinda alt="" cok
     * yaygin oldugu icin bu, gunluk kullanimda tetiklenen bir hataydi.
     *
     */
    #[DataProvider('bosNitelikSaglayici')]
    public function test_bos_nitelik_hata_firlatmaz(string $girdi): void
    {
        $temiz = HtmlSanitizer::clean($girdi);

        $this->assertIsString($temiz);
        $this->assertNotSame('', $temiz);
    }

    public function test_target_blank_link_rel_kazanir(): void
    {
        $temiz = HtmlSanitizer::clean('<a href="https://ornek.test" target="_blank">git</a>');

        $this->assertStringContainsString('rel="noopener noreferrer"', $temiz);
    }

    public function test_bos_ve_duz_metin_girdi(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean(''));
        $this->assertSame('<p>Link yok.</p>', HtmlSanitizer::clean('<p>Link yok.</p>'));
    }
}
