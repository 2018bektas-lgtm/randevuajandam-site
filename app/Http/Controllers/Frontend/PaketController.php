<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\KlinikKayitRequest;
use App\Models\BelgeErisimLog;
use App\Models\Brans;
use App\Models\Doktor;
use App\Models\DoktorMezuniyetBelgesi;
use App\Models\Hasta;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\SiteAyari;
use App\Models\UyelikOdeme;
use App\Models\Unvan;
use App\Rules\TcKimlikNo;
use App\Services\Edevlet\BelgeDogrulamaService;
use App\Services\Meslek\MeslekEslesmeService;
use App\Notifications\MeslekBelgesiAdminBildirimi;
use App\Services\PaymentDriverService;
use App\Services\PaytrService;
use App\Services\ReferansService;
use App\Support\MetaPixel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaketController extends Controller
{
    /**
     * Public pricing page. Logged-in doctors go to package selection (not forced checkout).
     */
    public function index()
    {
        $doktor = Auth::guard('doktor')->user();
        if ($doktor) {
            if (! $doktor->canProceedToPayment()) {
                return redirect()->route('frontend.hekim.meslek.bekleme');
            }

            // Her zaman paket grid — kayit_paket_id ile ödemeye kilitleme yok
            return redirect()->route('frontend.hekim.paket_sec', ['degistir' => 1]);
        }

        $bireyselPaketler = Paket::query()
            ->with('sistemOzellikleri')
            ->where('tur', 'bireysel')
            ->where('aktif_mi', true)
            ->orderBy('aylik_fiyat')
            ->get();

        $klinikPaketler = Paket::query()
            ->with('sistemOzellikleri')
            ->where('tur', 'klinik')
            ->where('aktif_mi', true)
            ->orderBy('sira')
            ->orderBy('aylik_fiyat')
            ->get();

        $maxYillikTasarrufYuzde = $this->maxYillikTasarrufYuzde(
            $bireyselPaketler->concat($klinikPaketler)
        );

        // Bireysel / klinik ayrı matris: yalnızca o türde gerçekten olan özellikler + klinik bayrakları
        $matrisBireysel = \App\Support\PaketOzellikKatalogu::matrisIcinGruplu($bireyselPaketler, 'bireysel');
        $matrisKlinik = \App\Support\PaketOzellikKatalogu::matrisIcinGruplu($klinikPaketler, 'klinik');

        MetaPixel::queue('ViewContent', MetaPixel::content(
            'Paketler',
            'product_group',
            'paketler',
            null,
            'TRY',
            ['content_category' => 'subscription']
        ));

        return view('frontend.paketler.index', compact(
            'bireyselPaketler',
            'klinikPaketler',
            'maxYillikTasarrufYuzde',
            'matrisBireysel',
            'matrisKlinik'
        ));
    }

    /**
     * Best yearly savings % vs 12 × monthly (indirimli or list).
     */
    protected function maxYillikTasarrufYuzde($paketler): int
    {
        $max = 0;
        foreach ($paketler as $p) {
            $aylik = (float) ($p->aylik_indirimli_fiyat ?? $p->aylik_fiyat ?? 0);
            $yillik = (float) ($p->yillik_indirimli_fiyat ?? $p->yillik_fiyat ?? 0);
            if ($aylik <= 0 || $yillik <= 0) {
                continue;
            }
            $onIkiAy = $aylik * 12;
            if ($onIkiAy <= $yillik) {
                continue;
            }
            $pct = (int) round((($onIkiAy - $yillik) / $onIkiAy) * 100);
            $max = max($max, $pct);
        }

        return $max;
    }

    /**
     * Show the doctor registration form.
     * Akış: önce paket seç (/paketler) → kayıt → meslek → ödeme (aynı paket).
     */
    public function kayitFormu(Request $request)
    {
        $paketId = $request->query('paket') ?: session('kayit_paket_id');
        $periyot = $request->query('periyot', session('kayit_periyot', 'aylik'));
        if (! in_array($periyot, ['aylik', 'yillik'], true)) {
            $periyot = 'aylik';
        }

        $secilenPaket = $paketId
            ? Paket::where('aktif_mi', true)->find($paketId)
            : null;

        // Referans kodunu session'da tut (paket seçimine gidişte kaybolmasın)
        $refKod = $request->query('ref')
            ?: $request->input('referans_kodu')
            ?: session('ra_ref')
            ?: $request->cookie(config('referans.cookie_name', 'ra_ref'));
        if (is_string($refKod) && preg_match('/^[A-Za-z0-9]{4,16}$/', $refKod)) {
            $refKod = strtoupper($refKod);
            session(['ra_ref' => $refKod]);
        } else {
            $refKod = session('ra_ref');
        }

        if (! $secilenPaket) {
            $params = array_filter(['ref' => $refKod]);

            return redirect()
                ->route('frontend.paketler', $params)
                ->with('basarili', $refKod
                    ? 'Referans kodunuz kaydedildi. Devam için bir paket seçin — kayıtta otomatik uygulanır.'
                    : 'Kayıt için önce bir paket seçin. Onay sonrası aynı paketle ödemeye geçersiniz.');
        }

        session([
            'kayit_paket_id' => $secilenPaket->id,
            'kayit_periyot' => $periyot,
        ]);
        if ($refKod) {
            session(['ra_ref' => $refKod]);
        }

        $branslar = Brans::orderBy('ad')->get();
        $unvanlar = Unvan::orderBy('ad')->get();

        $paketFiyat = $periyot === 'yillik'
            ? (float) ($secilenPaket->yillik_indirimli_fiyat ?? $secilenPaket->yillik_fiyat ?? 0)
            : (float) ($secilenPaket->aylik_indirimli_fiyat ?? $secilenPaket->aylik_fiyat ?? 0);
        if ($paketFiyat <= 0) {
            $paketFiyat = $periyot === 'yillik'
                ? (float) $secilenPaket->yillik_fiyat
                : (float) $secilenPaket->aylik_fiyat;
        }

        MetaPixel::queue('AddToCart', array_merge(
            MetaPixel::money($paketFiyat),
            [
                'content_name' => $secilenPaket->ad,
                'content_ids' => [(string) $secilenPaket->id],
                'content_type' => 'product',
                'num_items' => 1,
            ]
        ));

        return view('frontend.hekim.kayit', compact('branslar', 'unvanlar', 'secilenPaket', 'periyot'));
    }

    /**
     * AJAX: Mezuniyet belgelerini yükle (e-Devlet otomatik doğrulama YOK).
     * Kayıt sonrası yönetici manuel onaylar.
     * Session: mezuniyet_dogrulama_list
     */
    public function mezuniyetDogrula(
        Request $request,
        BelgeDogrulamaService $edevlet,
        MeslekEslesmeService $eslesme
    ) {
        $request->validate([
            'ad_soyad' => ['required', 'string', 'max:255'],
            'tc_kimlik_no' => ['required', 'string', 'size:11', new TcKimlikNo],
            'edevlet_barkod' => ['nullable', 'string', 'max:500'],
            'edevlet_barkodlar' => ['nullable', 'string', 'max:1000'],
            'mezuniyet_belgesi' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'mezuniyet_belgeleri' => ['nullable', 'array', 'max:8'],
            'mezuniyet_belgeleri.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'tc_kimlik_no.required' => 'T.C. kimlik zorunludur.',
            'ad_soyad.required' => 'Ad soyad zorunludur.',
            'mezuniyet_belgeleri.max' => 'En fazla 8 belge yükleyebilirsiniz.',
            'mezuniyet_belgeleri.*.max' => 'Her belge en fazla 5 MB olabilir.',
        ]);

        $tc = preg_replace('/\D/', '', (string) $request->tc_kimlik_no) ?? '';
        $ad = trim((string) $request->ad_soyad);

        $barkodlar = [];
        foreach (['edevlet_barkodlar', 'edevlet_barkod'] as $field) {
            if ($request->filled($field)) {
                foreach (preg_split('/[\s,;]+/', (string) $request->input($field)) as $b) {
                    $b = strtoupper(trim($b));
                    if ($b !== '' && preg_match('/^[A-Z0-9\-]{8,64}$/', $b)) {
                        $barkodlar[] = $b;
                    }
                }
            }
        }
        $barkodlar = array_values(array_unique($barkodlar));

        $files = [];
        if ($request->hasFile('mezuniyet_belgeleri')) {
            foreach ($request->file('mezuniyet_belgeleri') as $f) {
                if ($f) {
                    $files[] = $f;
                }
            }
        }
        if ($request->hasFile('mezuniyet_belgesi')) {
            $files[] = $request->file('mezuniyet_belgesi');
        }

        // Ürün kararı: en az bir dosya zorunlu (sadece barkod ile otomatik doğrulama yok)
        if ($files === []) {
            return response()->json([
                'ok' => false,
                'error' => 'En az bir mezuniyet / meslek belgesi (PDF veya görsel) yükleyin. Belgeleriniz yönetici onayına gidecektir.',
            ], 422);
        }

        $items = [];
        foreach ($files as $idx => $file) {
            $label = $file->getClientOriginalName() ?: ('Belge '.($idx + 1));
            $uploadRel = $file->store('private/mezuniyet-yukleme', 'local');
            $barkod = $barkodlar[$idx] ?? null;
            $items[] = [
                'pdf_path' => $uploadRel,
                'dosya_adi' => $label,
                'barkod' => $barkod,
                'tc' => $tc,
                'ad_soyad_belge' => $ad,
                'auto_onay_uygun' => false,
                'tc_ok' => null,
                'ad_ok' => null,
                'ad_skor' => null,
                'nedenler' => ['manuel_yukleme'],
                'uyari' => 'Yönetici onayı bekleniyor',
                'program' => null,
                'universite' => null,
                'diploma_no' => null,
                'mezuniyet_tarihi' => null,
                'mezuniyet_satiri' => null,
            ];
        }

        $primary = $items[0];
        $list = [
            'items' => $items,
            'auto_onay_uygun' => false,
            'adet' => count($items),
            'basarili_adet' => 0,
            'primary_index' => 0,
            'manuel_yukleme' => true,
        ];

        session([
            'mezuniyet_dogrulama_list' => $list,
            'mezuniyet_dogrulama' => $primary,
        ]);

        return response()->json([
            'ok' => true,
            'auto_onay_uygun' => false,
            'adet' => count($items),
            'basarili_adet' => 0,
            'payload' => $this->publicMezuniyetPayload($primary),
            'items' => array_map(fn ($it) => $this->publicMezuniyetPayload($it), $items),
            'ozet' => count($items).' belge yüklendi. Kaydı tamamlayın; yönetici onayından sonra ödemeye geçebilirsiniz.',
            'uyari' => null,
            'all_tc_ad_ok' => false,
            'manuel_onay' => true,
        ]);
    }

    /**
     * Tek belge / barkod için e-Devlet + parse + eşleşme.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $file
     * @return array<string, mixed>
     */
    protected function processOneMezuniyetBelge(
        BelgeDogrulamaService $edevlet,
        MeslekEslesmeService $eslesme,
        string $ad,
        string $tc,
        ?string $ip,
        $file,
        ?string $barkodHint,
        string $label
    ): array {
        $uploadRel = null;
        $barkod = $barkodHint ? strtoupper(trim($barkodHint)) : null;
        $originalName = $label;

        if ($file) {
            $uploadRel = $file->store('private/mezuniyet-yukleme', 'local');
            $originalName = $file->getClientOriginalName() ?: $label;
            $abs = Storage::disk('local')->path($uploadRel);
            $mime = $file->getMimeType() ?? '';
            if (str_contains($mime, 'pdf') || str_ends_with(strtolower($originalName), '.pdf')) {
                $local = $edevlet->parseYuklenenPdf($abs, $uploadRel);
                if ($local['ok'] && ! empty($local['parsed']['barkod']) && ! $barkod) {
                    $barkod = $local['parsed']['barkod'];
                }
            }
        }

        if (! $barkod) {
            // Barkodsuz görsel/PDF: sadece local parse
            if ($uploadRel) {
                $local = $edevlet->parseYuklenenPdf(Storage::disk('local')->path($uploadRel), $uploadRel);
                if ($local['ok'] && ! empty($local['parsed'])) {
                    $parsed = $local['parsed'];
                    $match = $eslesme->eslestir($ad, $tc, $parsed);
                    $payload = $this->buildMezuniyetSessionPayload(
                        $parsed,
                        $match,
                        $uploadRel,
                        null,
                        (bool) ($match['auto_onay_uygun'] ?? false),
                        'Barkod bulunamadı; dosya metinden okundu. '
                        .($match['auto_onay_uygun'] ? '' : 'Eşleşme eksikse talebiniz incelenecek.')
                    );
                    $payload['dosya_adi'] = $originalName;

                    return $payload;
                }
            }
            throw new \RuntimeException('Barkod bulunamadı ('.$originalName.'). Barkodu yazın veya barkodlu PDF yükleyin.');
        }

        $result = $edevlet->dogrulaMezunBelgesi($barkod, $tc, $ip);
        if (! $result['ok'] || empty($result['parsed'])) {
            if ($uploadRel) {
                $local = $edevlet->parseYuklenenPdf(Storage::disk('local')->path($uploadRel), $uploadRel);
                if ($local['ok'] && ! empty($local['parsed'])) {
                    $parsed = $local['parsed'];
                    if (empty($parsed['barkod'])) {
                        $parsed['barkod'] = $barkod;
                    }
                    $match = $eslesme->eslestir($ad, $tc, $parsed);
                    $payload = $this->buildMezuniyetSessionPayload(
                        $parsed,
                        $match,
                        $uploadRel,
                        null,
                        (bool) ($match['auto_onay_uygun'] ?? false),
                        'e-Devlet anlık yanıt vermedi; yüklenen dosya okundu. '
                        .($result['error'] ?? '')
                    );
                    $payload['dosya_adi'] = $originalName;

                    return $payload;
                }
            }
            // Sadece barkod, PDF yok — e-Devlet fail
            throw new \RuntimeException(($result['error'] ?? 'Doğrulama başarısız').' ('.$barkod.')');
        }

        $parsed = $result['parsed'];
        $match = $eslesme->eslestir($ad, $tc, $parsed);
        $payload = $this->buildMezuniyetSessionPayload(
            $parsed,
            $match,
            $result['pdf_path'] ?? $uploadRel,
            $result['log_id'] ?? null,
            (bool) ($match['auto_onay_uygun'] ?? false),
            $match['auto_onay_uygun'] ? null : ($match['sonuc_ozet'] ?? null)
        );
        $payload['dosya_adi'] = $originalName;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @param  array<string, mixed>  $match
     * @return array<string, mixed>
     */
    protected function buildMezuniyetSessionPayload(
        array $parsed,
        array $match,
        ?string $pdfPath,
        ?int $logId,
        bool $autoOnay,
        ?string $uyari
    ): array {
        $eslesme = app(MeslekEslesmeService::class);

        return [
            'verified_at' => now()->toIso8601String(),
            'barkod' => $parsed['barkod'] ?? null,
            'tc' => $parsed['tc'] ?? null,
            'ad_soyad_belge' => $parsed['ad_soyad'] ?? null,
            'program' => $parsed['program'] ?? null,
            'universite' => $parsed['universite'] ?? null,
            'fakulte' => $parsed['fakulte'] ?? null,
            'bolum' => $parsed['bolum'] ?? null,
            'diploma_no' => $parsed['diploma_no'] ?? null,
            'diploma_notu' => $parsed['diploma_notu'] ?? null,
            'mezuniyet_tarihi' => $parsed['mezuniyet_tarihi'] ?? null,
            'mezuniyet_satiri' => $eslesme->mezuniyetSatiri($parsed),
            'pdf_path' => $pdfPath,
            'log_id' => $logId,
            'tc_ok' => (bool) ($match['tc_ok'] ?? false),
            'ad_ok' => (bool) ($match['ad_ok'] ?? false),
            'ad_skor' => $match['ad_skor'] ?? 0,
            'auto_onay_uygun' => $autoOnay && (bool) ($match['auto_onay_uygun'] ?? false),
            'onerilen_unvan' => $match['onerilen_unvan'] ?? null,
            'onerilen_brans' => $match['onerilen_brans'] ?? null,
            'onerilen_brans_id' => $match['onerilen_brans_id'] ?? null,
            'nedenler' => $match['nedenler'] ?? [],
            'kontroller' => $match['kontroller'] ?? [],
            'sonuc_baslik' => $match['sonuc_baslik'] ?? null,
            'sonuc_ozet' => $match['sonuc_ozet'] ?? null,
            'uyari' => $uyari,
            'ham_parse' => $parsed,
            'dosya_adi' => null,
            'kontroller' => $match['kontroller'] ?? [],
            'sonuc_baslik' => $match['sonuc_baslik'] ?? null,
            'sonuc_ozet' => $match['sonuc_ozet'] ?? null,
            'nedenler' => $match['nedenler'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function publicMezuniyetPayload(array $payload): array
    {
        return [
            'barkod' => $payload['barkod'] ?? null,
            'ad_soyad_belge' => $payload['ad_soyad_belge'] ?? null,
            'program' => $payload['program'] ?? null,
            'universite' => $payload['universite'] ?? null,
            'fakulte' => $payload['fakulte'] ?? null,
            'bolum' => $payload['bolum'] ?? null,
            'diploma_no' => $payload['diploma_no'] ?? null,
            'diploma_notu' => $payload['diploma_notu'] ?? null,
            'mezuniyet_tarihi' => $payload['mezuniyet_tarihi'] ?? null,
            'mezuniyet_satiri' => $payload['mezuniyet_satiri'] ?? null,
            'tc_ok' => $payload['tc_ok'] ?? false,
            'ad_ok' => $payload['ad_ok'] ?? false,
            'ad_skor' => $payload['ad_skor'] ?? 0,
            'auto_onay_uygun' => $payload['auto_onay_uygun'] ?? false,
            'onerilen_unvan' => $payload['onerilen_unvan'] ?? null,
            'onerilen_brans' => $payload['onerilen_brans'] ?? null,
            'onerilen_brans_id' => $payload['onerilen_brans_id'] ?? null,
            'nedenler' => $payload['nedenler'] ?? [],
            'kontroller' => $payload['kontroller'] ?? [],
            'sonuc_baslik' => $payload['sonuc_baslik'] ?? null,
            'sonuc_ozet' => $payload['sonuc_ozet'] ?? null,
            'uyari' => $payload['uyari'] ?? null,
            'dosya_adi' => $payload['dosya_adi'] ?? null,
        ];
    }

    /**
     * Session'dan doğrulanmış belge listesi (çoklu veya tekil).
     *
     * @return array{items: array<int, array>, auto_onay_uygun: bool, primary: ?array}
     */
    protected function mezuniyetSessionBundle(): array
    {
        $list = session('mezuniyet_dogrulama_list');
        if (is_array($list) && ! empty($list['items']) && is_array($list['items'])) {
            $items = $list['items'];
            $primary = $items[$list['primary_index'] ?? 0] ?? $items[0];

            return [
                'items' => $items,
                'auto_onay_uygun' => (bool) ($list['auto_onay_uygun'] ?? false),
                'primary' => $primary,
            ];
        }

        $single = session('mezuniyet_dogrulama');
        if (is_array($single) && (! empty($single['barkod']) || ! empty($single['pdf_path']))) {
            return [
                'items' => [$single],
                'auto_onay_uygun' => (bool) ($single['auto_onay_uygun'] ?? false),
                'primary' => $single,
            ];
        }

        return ['items' => [], 'auto_onay_uygun' => false, 'primary' => null];
    }

    /**
     * Handle the doctor registration.
     */
    public function kayitOl(Request $request)
    {
        $bundle = $this->mezuniyetSessionBundle();
        $mezunItems = $bundle['items'];
        $mezunSession = $bundle['primary'];
        $hasVerified = $mezunItems !== [];

        $request->validate([
            'ad_soyad' => 'required|string|max:255',
            'e_posta' => 'required|email|max:255|unique:doktorlar,e_posta',
            'sifre' => [
                'required',
                'string',
                'min:8',
                'regex:~^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]).+$~',
                'confirmed',
            ],
            'telefon' => ['required', 'string', 'regex:/^0\s\(5[0-9]{2}\)\s[0-9]{3}\s[0-9]{2}\s[0-9]{2}$/'],
            'tc_kimlik_no' => ['required', 'string', 'size:11', 'unique:doktorlar,tc_kimlik_no', new TcKimlikNo],
            'diploma_no' => [$hasVerified ? 'nullable' : 'required', 'string', 'max:64'],
            'edevlet_barkod' => ['nullable', 'string', 'max:500'],
            'meslek_belgesi' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'mezuniyet_belgeleri' => ['nullable', 'array', 'max:8'],
            'mezuniyet_belgeleri.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'unvan' => 'required|string|max:80',
            'il' => 'required|string|max:255',
            'ilce' => 'required|string|max:255',
            'branslar' => 'required|array|min:1',
            'branslar.*' => 'exists:branslar,id',
            'mezuniyet' => 'nullable|array',
            'mezuniyet.*' => 'nullable|string|max:255',
            'biyografi' => 'nullable|string',
            'kvkk_onay' => 'accepted',
            'sozlesme_onay' => 'accepted',
            'paket_id' => 'required|exists:paketler,id',
            'odeme_periyodu' => 'required|in:aylik,yillik',
        ], [
            'ad_soyad.required' => 'Ad Soyad alanı zorunludur.',
            'paket_id.required' => 'Kayıt için paket seçimi zorunludur.',
            'paket_id.exists' => 'Seçilen paket geçersiz. Lütfen paketler sayfasından yeniden seçin.',
            'e_posta.required' => 'E-posta adresi zorunludur.',
            'e_posta.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'e_posta.unique' => 'Bu e-posta adresi zaten sisteme kayıtlı.',
            'sifre.required' => 'Şifre alanı zorunludur.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
            'sifre.regex' => 'Şifreniz en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.',
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
            'telefon.required' => 'Telefon numarası zorunludur.',
            'telefon.regex' => 'Telefon numarası 0 (5xx) xxx xx xx formatında olmalıdır.',
            'tc_kimlik_no.required' => 'T.C. kimlik numarası zorunludur.',
            'tc_kimlik_no.unique' => 'Bu T.C. kimlik numarası ile kayıtlı bir hekim zaten var.',
            'diploma_no.required' => 'Diploma / tescil numarası zorunludur (mezuniyet doğrulaması yapılmadıysa).',
            'meslek_belgesi.mimes' => 'Belge PDF, JPG veya PNG olmalıdır.',
            'meslek_belgesi.max' => 'Belge en fazla 5 MB olabilir.',
            'unvan.required' => 'Mesleki unvan seçimi zorunludur.',
            'il.required' => 'Hizmet verilen il seçimi zorunludur.',
            'ilce.required' => 'Hizmet verilen ilçe seçimi zorunludur.',
            'branslar.required' => 'En az bir uzmanlık alanı / branş seçmelisiniz.',
            'kvkk_onay.accepted' => 'KVKK aydınlatma metnini kabul etmelisiniz.',
            'sozlesme_onay.accepted' => 'Kullanım koşullarını kabul etmelisiniz.',
        ]);

        $kayitPaket = Paket::where('aktif_mi', true)->findOrFail($request->input('paket_id'));
        // Excel: klinik ücretsiz Vitrin alamaz
        if ($kayitPaket->klinikPaketiMi() && $kayitPaket->ucretsizMi()) {
            return back()->withInput()->withErrors(['paket_id' => 'Klinikler ücretsiz Vitrin paketi kullanamaz. Lütfen ücretli bir klinik paketi seçin.']);
        }
        $kayitPeriyot = $request->input('odeme_periyodu', 'aylik');

        $ilModel = Il::where('ad', $request->il)->first();
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce)->first();

        $bransIsimleri = Brans::whereIn('id', $request->branslar)->pluck('ad')->toArray();
        $uzmanlikAlaniString = implode(', ', $bransIsimleri);

        $mezuniyetDizisi = array_values(array_filter($request->input('mezuniyet', []), function ($val) {
            return ! is_null($val) && trim($val) !== '';
        }));
        foreach ($mezunItems as $it) {
            if (! empty($it['mezuniyet_satiri'])) {
                array_unshift($mezuniyetDizisi, $it['mezuniyet_satiri']);
            }
        }
        $mezuniyetDizisi = array_values(array_unique($mezuniyetDizisi));

        $tc = preg_replace('/\D/', '', (string) $request->tc_kimlik_no) ?? '';

        $belgeRel = $hasVerified
            ? ($mezunSession['pdf_path'] ?? null)
            : null;
        if ($request->hasFile('meslek_belgesi')) {
            $belgeRel = $request->file('meslek_belgesi')->store('private/meslek-belgeleri', 'local');
        } elseif ($request->hasFile('mezuniyet_belgeleri')) {
            $first = $request->file('mezuniyet_belgeleri')[0] ?? null;
            if ($first) {
                $belgeRel = $first->store('private/meslek-belgeleri', 'local');
            }
        }
        if (! $belgeRel && ! $hasVerified) {
            return back()->withInput()->withErrors([
                'mezuniyet_belgeleri' => 'En az bir mezuniyet / meslek belgesi yüklemelisiniz.',
            ]);
        }
        if (! $belgeRel && $hasVerified) {
            $belgeRel = collect($mezunItems)->pluck('pdf_path')->filter()->first();
        }

        $diplomaNo = $hasVerified && ! empty($mezunSession['diploma_no'])
            ? (string) $mezunSession['diploma_no']
            : trim((string) $request->diploma_no);
        if ($hasVerified && ! empty($mezunSession['barkod'])) {
            $barkod = (string) $mezunSession['barkod'];
        } elseif ($request->filled('edevlet_barkod')) {
            $parts = preg_split('/[\s,;]+/', (string) $request->edevlet_barkod) ?: [];
            $barkod = strtoupper(trim((string) ($parts[0] ?? ''))) ?: null;
        } else {
            $barkod = null;
        }

        // Otomatik e-Devlet onayı kapalı — her zaman yönetici onayı
        $autoOnay = false;
        $meslekDurum = 'beklemede';
        $meslekNot = $hasVerified
            ? 'manuel_yukleme:'.count($mezunItems).' belge; yonetici onayi bekleniyor'
            : 'manuel_yukleme; yonetici onayi bekleniyor';

        $doktor = DB::transaction(function () use (
            $request,
            $uzmanlikAlaniString,
            $mezuniyetDizisi,
            $ilModel,
            $ilceModel,
            $tc,
            $belgeRel,
            $kayitPaket,
            $kayitPeriyot,
            $diplomaNo,
            $barkod,
            $meslekDurum,
            $meslekNot,
            $autoOnay,
            $hasVerified,
            $mezunItems
        ) {
            $doktor = Doktor::create([
                'ad_soyad' => $request->ad_soyad,
                'e_posta' => $request->e_posta,
                'sifre' => Hash::make($request->sifre),
                'telefon' => $request->telefon,
                'tc_kimlik_no' => $tc,
                'diploma_no' => $diplomaNo,
                'edevlet_barkod' => $barkod,
                'meslek_belge_yolu' => $belgeRel,
                'meslek_dogrulama_durumu' => $meslekDurum,
                'meslek_dogrulama_notu' => $meslekNot ? Str::limit((string) $meslekNot, 500) : null,
                'meslek_dogrulandi_at' => null,
                'meslek_dogrulayan_yonetici_id' => null,
                'il_id' => $ilModel?->id,
                'ilce_id' => $ilceModel?->id,
                'unvan' => $request->unvan,
                'uzmanlik_alani' => $uzmanlikAlaniString,
                'mezuniyet' => $mezuniyetDizisi,
                'biyografi' => $request->biyografi,
                'tur' => $kayitPaket->klinikPaketiMi() ? 'klinik' : 'bireysel',
                'paket_id' => null,
                'kayit_paket_id' => $kayitPaket->id,
                'kayit_periyot' => $kayitPeriyot,
                'odeme_periyodu' => null,
                'uyelik_baslangic' => null,
                'uyelik_bitis' => null,
                'iyzico_subscription_reference_code' => null,
                'iyzico_subscription_status' => null,
                'aktif_mi' => true,
                'platformda_gorunur' => false,
            ]);

            $doktor->branslar()->attach($request->branslar);

            $refKod = $request->input('referans_kodu')
                ?: $request->cookie(config('referans.cookie_name', 'ra_ref'))
                ?: session('ra_ref');
            app(ReferansService::class)->attachOnRegister($doktor, is_string($refKod) ? $refKod : null);
            app(ReferansService::class)->ensureKod($doktor);

            if ($hasVerified) {
                foreach ($mezunItems as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    DoktorMezuniyetBelgesi::create([
                        'doktor_id' => $doktor->id,
                        'barkod' => $item['barkod'] ?? null,
                        'tc_kimlik_no' => $item['tc'] ?? $tc,
                        'ad_soyad_belge' => $item['ad_soyad_belge'] ?? null,
                        'program' => $item['program'] ?? null,
                        'universite' => $item['universite'] ?? null,
                        'fakulte' => $item['fakulte'] ?? null,
                        'bolum' => $item['bolum'] ?? null,
                        'diploma_no' => $item['diploma_no'] ?? null,
                        'diploma_notu' => $item['diploma_notu'] ?? null,
                        'mezuniyet_tarihi' => $item['mezuniyet_tarihi'] ?? null,
                        'dogrulama_durumu' => 'beklemede',
                        'eslesme_skoru' => $item['ad_skor'] ?? null,
                        'eslesme_detay' => [
                            'tc_ok' => $item['tc_ok'] ?? null,
                            'ad_ok' => $item['ad_ok'] ?? null,
                            'nedenler' => $item['nedenler'] ?? ['manuel_yukleme'],
                            'dosya_adi' => $item['dosya_adi'] ?? null,
                        ],
                        'dosya_yolu' => $item['pdf_path'] ?? $belgeRel,
                        'ham_parse' => $item['ham_parse'] ?? null,
                        'edevlet_log_id' => $item['log_id'] ?? null,
                        'auto_onay_uygun' => false,
                        'onerilen_unvan' => $item['onerilen_unvan'] ?? null,
                        'onerilen_brans' => $item['onerilen_brans'] ?? null,
                    ]);
                }
            }

            return $doktor;
        });

        Auth::guard('doktor')->login($doktor);

        session()->forget(['kayit_paket_id', 'kayit_periyot', 'mezuniyet_dogrulama', 'mezuniyet_dogrulama_list']);

        if ($belgeRel && ($meslekDurum ?? '') === 'beklemede') {
            MeslekBelgesiAdminBildirimi::notifyAdmin($doktor, 'kayit');
        }

        MetaPixel::queueOnce(
            'complete_reg_hekim_'.$doktor->id,
            'CompleteRegistration',
            MetaPixel::content(
                'Hekim kaydı',
                'product',
                'hekim-'.$doktor->id,
                null,
                'TRY',
                [
                    'status' => 'pending_review',
                    'content_category' => $kayitPaket->klinikPaketiMi() ? 'klinik' : 'hekim',
                ]
            )
        );

        return redirect()
            ->route('frontend.hekim.meslek.bekleme')
            ->with('basarili', 'Kaydınız alındı. Belgeleriniz yönetici onayına gönderildi. Onay sonrası paket ödemesine geçebilirsiniz.');
    }

    /**
     * Meslek belgesi onay bekleme ekranı + reddedildiyse yeniden yükleme.
     */
    public function meslekBekleme()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        if ($doktor->canProceedToPayment()) {
            return redirect()->to($doktor->checkoutUrlAfterMeslek());
        }

        $doktor->loadMissing('kayitPaketi');

        return view('frontend.hekim.meslek_bekleme', compact('doktor'));
    }

    /**
     * Poll: meslek durumu JSON (bekleme ekranı auto-redirect).
     */
    public function meslekDurumJson()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return response()->json(['ok' => false], 401);
        }

        $doktor->refresh();

        return response()->json([
            'ok' => true,
            'durum' => $doktor->meslek_dogrulama_durumu ?? 'beklemede',
            'can_proceed' => $doktor->canProceedToPayment(),
            'redirect' => $doktor->canProceedToPayment()
                ? $doktor->checkoutUrlAfterMeslek()
                : null,
        ]);
    }

    /**
     * Hekim kendi meslek belgesini görüntüler (private storage).
     */
    public function meslekBelgeGoster()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        $path = (string) ($doktor->meslek_belge_yolu ?? '');
        if ($path === '') {
            abort(404);
        }

        BelgeErisimLog::kaydet($doktor->id, 'doktor', 'meslek_belgesi');

        if (str_starts_with($path, 'private/') || str_starts_with($path, 'meslek-belgeleri/')) {
            $diskPath = str_starts_with($path, 'private/') ? $path : 'private/'.$path;
            if (! Storage::disk('local')->exists($diskPath) && Storage::disk('local')->exists($path)) {
                $diskPath = $path;
            }
            if (! Storage::disk('local')->exists($diskPath)) {
                abort(404);
            }

            return Storage::disk('local')->response($diskPath, basename($diskPath), [
                'Content-Type' => Storage::disk('local')->mimeType($diskPath) ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.basename($diskPath).'"',
            ]);
        }

        $full = public_path(ltrim($path, '/'));
        if (is_file($full)) {
            return response()->file($full);
        }

        abort(404);
    }

    /**
     * Reddedilen / eksik belgeyi yeniden yükle.
     */
    public function meslekBelgeYenile(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.giris');
        }

        if ($doktor->isMeslekOnayli()) {
            return redirect()->to($doktor->checkoutUrlAfterMeslek());
        }

        $request->validate([
            'tc_kimlik_no' => ['required', 'string', 'size:11', 'unique:doktorlar,tc_kimlik_no,'.$doktor->id, new TcKimlikNo],
            'diploma_no' => ['required', 'string', 'min:3', 'max:64'],
            'edevlet_barkod' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9\-]+$/'],
            'meslek_belgesi' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'tc_kimlik_no.required' => 'T.C. kimlik numarası zorunludur.',
            'diploma_no.required' => 'Diploma / tescil numarası zorunludur.',
            'meslek_belgesi.required' => 'Belge yüklemeniz zorunludur.',
        ]);

        $tc = preg_replace('/\D/', '', (string) $request->tc_kimlik_no) ?? '';
        $belgeRel = $request->file('meslek_belgesi')->store(
            'private/meslek-belgeleri',
            'local'
        );

        $doktor->forceFill([
            'tc_kimlik_no' => $tc,
            'diploma_no' => trim((string) $request->diploma_no),
            'edevlet_barkod' => $request->filled('edevlet_barkod')
                ? strtoupper(trim((string) $request->edevlet_barkod))
                : null,
            'meslek_belge_yolu' => $belgeRel,
            'meslek_dogrulama_durumu' => 'beklemede',
            'meslek_dogrulama_notu' => null,
            'meslek_dogrulandi_at' => null,
            'meslek_dogrulayan_yonetici_id' => null,
        ])->save();

        MeslekBelgesiAdminBildirimi::notifyAdmin($doktor->fresh() ?? $doktor, 'yenile');

        return back()->with('basarili', 'Belgeleriniz yeniden gönderildi. İnceleme sonrası bilgilendirileceksiniz.');
    }

    /**
     * Show successful registration page.
     * Web sitesi paketinde domain kurulmamışsa önce domain adımına yönlendir.
     */
    public function basarili()
    {
        $doktor = Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.paketler');
        }

        // Deneme aktifken domain adımına zorlama (starter'da web yok)
        // Ödeme sonrası zorunlu domain adımı (atlandıysa veya tamamlandıysa değil)
        // Havale yeni onaylandığında domain zorlamadan önce başarı ekranı gösterilsin
        $yeniHavaleOnay = UyelikOdeme::sonOnayliHavaleForDoktor((int) $doktor->id, 7);
        if (
            ! $doktor->isOnTrial()
            && ! $yeniHavaleOnay
            && $doktor->needsWebsiteDomainOnboarding()
            && ! session('onboarding_domain_skipped')
            && ! session('onboarding_domain_done')
            && ! session('plain_api_secret')
        ) {
            return redirect()->route('frontend.hekim.onboarding.domain');
        }

        $bekleyenHavale = UyelikOdeme::bekleyenHavaleForDoktor((int) $doktor->id);
        $sonOnayliHavale = ! $bekleyenHavale
            ? UyelikOdeme::sonOnayliHavaleForDoktor((int) $doktor->id, 30)
            : null;

        if ($doktor->klinikSahibiMi() && $doktor->klinik) {
            $klinik = $doktor->klinik;

            return view('frontend.klinik.basarili', compact('klinik', 'bekleyenHavale', 'sonOnayliHavale', 'doktor'));
        }

        return view('frontend.paketler.basarili', compact('doktor', 'bekleyenHavale', 'sonOnayliHavale'));
    }

    /**
     * Üyelik aktifleşince: session'daki domaini kur; yoksa (web paketi) sonradan domain adımı.
     */
    protected function redirectAfterMembership(?Doktor $doktor = null)
    {
        $doktor = $doktor ?? Auth::guard('doktor')->user();
        if (! $doktor) {
            return redirect()->route('frontend.hekim.basarili');
        }

        $doktor = $doktor->fresh(['paket', 'klinik', 'webSite']);
        $pending = session(HekimOnboardingController::SESSION_PENDING);
        $flash = [];

        if (is_array($pending) && ! empty($pending['domain']) && ! empty($pending['mode'])) {
            try {
                $provisioning = app(\App\Services\WebsiteProvisioningService::class);
                $mode = $pending['mode'] === 'byod' ? 'byod' : 'included';
                $target = $pending['target'] ?? 'doctor';

                if ($target === 'clinic' && $doktor->klinik) {
                    $result = $provisioning->provisionKlinik($doktor->klinik, $pending['domain'], $mode);
                } else {
                    $result = $provisioning->provisionDoktor($doktor, $pending['domain'], $mode);
                }

                session()->forget(HekimOnboardingController::SESSION_PENDING);
                $flash = [
                    'basarili' => 'Üyelik aktif. Domain kuruldu: '.$result['domain'],
                    'plain_api_secret' => $result['plain_secret'],
                    'onboarding_domain_done' => $result['domain'],
                ];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Post-pay domain provision failed', [
                    'error' => $e->getMessage(),
                    'domain' => $pending['domain'] ?? null,
                ]);
                // Domain seçilmişti ama kurulum başarısız → onboarding'e dön
                return redirect()
                    ->route('frontend.hekim.onboarding.domain')
                    ->with('hata', 'Üyelik aktif; domain kurulumu tamamlanamadı: '.$e->getMessage().' Lütfen tekrar deneyin.');
            }
        }

        $redirect = redirect()->route('frontend.hekim.basarili');
        foreach ($flash as $k => $v) {
            $redirect = $redirect->with($k, $v);
        }

        // Domain hiç seçilmediyse ve web paketi varsa sonradan kurulum
        if (
            empty($flash)
            && $doktor->needsWebsiteDomainOnboarding()
            && ! session('onboarding_domain_skipped')
        ) {
            return redirect()->route('frontend.hekim.onboarding.domain');
        }

        return $redirect;
    }

    /**
     * Show the clinic registration/purchase form.
     */
    public function klinikKayitFormu(Request $request)
    {
        $paketId = $request->query('paket');
        $periyot = $request->query('periyot', 'aylik');

        $secilenPaket = Paket::where('aktif_mi', true)->find($paketId);

        if (! $secilenPaket || ! $secilenPaket->klinikPaketiMi()) {
            $secilenPaket = Paket::where('aktif_mi', true)->where('tur', 'klinik')->first();
        }

        if (! $secilenPaket) {
            return redirect()->route('frontend.paketler')->with('hata', 'Lütfen geçerli bir klinik paketi seçin.');
        }

        $iller = Il::orderBy('ad')->get();
        $branslar = Brans::orderBy('ad')->get();
        $unvanlar = Unvan::orderBy('ad')->get();

        return view('frontend.klinik.kayit', compact('secilenPaket', 'periyot', 'iller', 'branslar', 'unvanlar'));
    }

    /**
     * Handle the clinic registration and simulated payment.
     */
    public function klinikKayitOl(KlinikKayitRequest $request)
    {
        $paket = Paket::findOrFail($request->paket_id);
        if (! $paket->klinikPaketiMi() || $paket->ucretsizMi()) {
            return back()->withInput()->withErrors(['paket_id' => 'Geçerli bir ücretli klinik paketi seçin. Ücretsiz Vitrin klinik için kullanılamaz.']);
        }

        $ilModel = Il::find($request->il_id);
        $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce_id)->first();
        $bransIsimleri = Brans::whereIn('id', $request->branslar)->pluck('ad')->toArray();
        $uzmanlikAlaniString = implode(', ', $bransIsimleri);

        $periodPrice = $request->odeme_periyodu === 'yillik'
            ? (float) $paket->yillik_fiyat
            : (float) $paket->aylik_fiyat;
        $discounted = $request->odeme_periyodu === 'yillik'
            ? $paket->yillik_indirimli_fiyat
            : $paket->aylik_indirimli_fiyat;
        $tutar = $discounted !== null && (float) $discounted > 0
            ? (float) $discounted
            : $periodPrice;
        $isFree = $tutar <= 0;

        $doktor = DB::transaction(function () use ($request, $uzmanlikAlaniString, $ilModel, $ilceModel) {
            $doktor = Doktor::create([
                'ad_soyad' => $request->ad_soyad,
                'e_posta' => $request->doktor_eposta,
                'sifre' => Hash::make($request->sifre),
                'telefon' => $request->doktor_telefon,
                'il_id' => $ilModel?->id,
                'ilce_id' => $ilceModel?->id,
                'unvan' => $request->unvan,
                'uzmanlik_alani' => $uzmanlikAlaniString,
                'tur' => 'klinik',
                'klinik_adi' => $request->klinik_adi,
                'paket_id' => null,
                'aktif_mi' => true,
                'platformda_gorunur' => false,
                'kayit_paket_id' => $request->paket_id,
                'kayit_periyot' => $request->odeme_periyodu,
            ]);
            $doktor->branslar()->attach($request->branslar);
            $doktor->randevuAyari()->create([
                'aktif_mi' => true,
                'sure' => 30,
                'fiyat' => 0,
            ]);

            return $doktor;
        });

        Auth::guard('doktor')->login($doktor);

        MetaPixel::queueOnce(
            'complete_reg_klinik_'.$doktor->id,
            'CompleteRegistration',
            MetaPixel::content(
                'Klinik kaydı',
                'product',
                'klinik-reg-'.$doktor->id,
                $tutar,
                'TRY',
                ['status' => true, 'content_category' => 'klinik']
            )
        );

        $kurulum = [
            'klinik_adi' => $request->klinik_adi,
            'telefon' => $request->telefon,
            'e_posta' => $request->e_posta,
            'adres' => $request->adres,
            'il_id' => $request->il_id,
            'ilce_id' => $request->ilce_id,
        ];

        if ($isFree) {
            $this->activateClinicMembershipLocal($doktor, $paket, $request->odeme_periyodu, $kurulum, 'free_klinik_'.Str::random(10));
            MetaPixel::queueOnce(
                'subscribe_free_klinik_'.$doktor->id.'_'.$paket->id,
                'Subscribe',
                array_merge(
                    MetaPixel::money(0),
                    [
                        'content_name' => $paket->ad,
                        'content_ids' => [(string) $paket->id],
                        'predicted_ltv' => 0,
                    ]
                )
            );

            return $this->redirectAfterMembership($doktor->fresh());
        }

        return app(PaymentDriverService::class)->startCheckout(
            $doktor,
            $paket,
            $request->odeme_periyodu,
            $tutar,
            $kurulum,
            $request
        );
    }

    /**
     * Show the clinic transition form for individual doctors.
     */
    public function gecisFormu()
    {
        $doktor = Auth::guard('doktor')->user();

        if (! $doktor->bireyselMi()) {
            return redirect()->route('hekim.panel')->with('hata', 'Zaten bir kliniğe üyesiniz.');
        }

        $paketler = Paket::where('aktif_mi', true)->where('tur', 'klinik')->orderBy('sira')->get();
        $iller = Il::orderBy('ad')->get();

        return view('klinik.gecis', compact('doktor', 'paketler', 'iller'));
    }

    /**
     * Handle the clinic transition process (Upgrade).
     */
    public function gecisYap(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();

        if (! $doktor->bireyselMi()) {
            return redirect()->route('hekim.panel')->with('hata', 'Zaten bir kliniğe üyesiniz.');
        }

        $request->validate([
            'klinik_adi' => 'required|string|max:255',
            'telefon' => 'required|string',
            'e_posta' => 'nullable|email|max:255',
            'adres' => 'required|string',
            'il_id' => 'required|exists:iller,id',
            'ilce_id' => 'required|string|max:255|exists:ilceler,ad',
            'paket_id' => 'required|exists:paketler,id',
            'odeme_periyodu' => 'required|in:aylik,yillik',
        ]);

        $paket = Paket::findOrFail($request->paket_id);
        if (! $paket->klinikPaketiMi()) {
            return back()->withErrors(['paket_id' => 'Lütfen geçerli bir klinik paketi seçin.']);
        }

        $periodPrice = $request->odeme_periyodu === 'yillik'
            ? (float) $paket->yillik_fiyat
            : (float) $paket->aylik_fiyat;
        $discounted = $request->odeme_periyodu === 'yillik'
            ? $paket->yillik_indirimli_fiyat
            : $paket->aylik_indirimli_fiyat;
        $tutar = $discounted !== null && (float) $discounted > 0
            ? (float) $discounted
            : $periodPrice;

        $kurulum = $request->only(['klinik_adi', 'telefon', 'e_posta', 'adres', 'il_id', 'ilce_id']);

        if ($tutar <= 0) {
            $this->activateClinicMembershipLocal($doktor, $paket, $request->odeme_periyodu, $kurulum, 'free_gecis_'.Str::random(10));

            return $this->redirectAfterMembership($doktor->fresh());
        }

        return app(PaymentDriverService::class)->startCheckout(
            $doktor,
            $paket,
            $request->odeme_periyodu,
            $tutar,
            $kurulum,
            $request
        );
    }

    /**
     * PayTR iframe oturumu başlat (klinik kayıt / geçiş / paket).
     *
     * @param  array<string, mixed>  $kurulum
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function startPaytrCheckout(Doktor $doktor, Paket $paket, string $periyot, float $tutar, array $kurulum, Request $request)
    {
        $paytr = app(PaytrService::class);
        if (! $paytr->isConfigured()) {
            return back()->withInput()->withErrors([
                'paket_id' => 'Kartlı ödeme (PayTR) yapılandırılmamış. Yöneticiye bildirin veya havale ile paket-ode ekranını kullanın.',
            ]);
        }

        $refFiyat = app(ReferansService::class)->indirimliTutar($doktor, $tutar);
        $tutar = $refFiyat['tutar'];
        $kurulum = array_merge($kurulum, [
            'tutar_brut' => $refFiyat['brut'],
            'referans_indirim_yuzde' => $refFiyat['indirim_yuzde'],
        ]);

        $merchantOid = $paytr->makeMerchantOid();
        UyelikOdeme::create([
            'doktor_id' => $doktor->id,
            'paket_id' => $paket->id,
            'odeme_yontemi' => 'paytr',
            'provider' => 'paytr',
            'odeme_periyodu' => $periyot,
            'tutar' => $tutar,
            'durum' => 'beklemede',
            'merchant_oid' => $merchantOid,
            'kurulum_verisi' => $kurulum,
        ]);

        $tokenResult = $paytr->createIframeToken([
            'merchant_oid' => $merchantOid,
            'email' => (string) $doktor->e_posta,
            'payment_amount' => $tutar,
            'user_name' => (string) $doktor->ad_soyad,
            'user_address' => (string) ($doktor->adres ?: ($doktor->il?->ad ?? 'Turkiye')),
            'user_phone' => (string) $doktor->telefon,
            'user_ip' => $request->ip(),
            'basket_name' => 'Randevu Ajandam - '.$paket->ad.' ('.$periyot.')',
        ]);

        if (($tokenResult['status'] ?? '') !== 'success') {
            UyelikOdeme::where('merchant_oid', $merchantOid)->update(['durum' => 'reddedildi']);

            return back()->withInput()->withErrors([
                'paket_id' => $tokenResult['errorMessage'] ?? 'PayTR ödeme oturumu açılamadı.',
            ]);
        }

        session(['paytr_iframe_token_'.$merchantOid => $tokenResult['token']]);

        MetaPixel::queue('InitiateCheckout', array_merge(
            MetaPixel::money((float) $tutar),
            [
                'content_name' => $paket->ad,
                'content_ids' => [(string) $paket->id],
                'content_type' => 'product',
                'num_items' => 1,
            ]
        ));

        return redirect()->route('frontend.odeme.paytr.iframe', ['merchantOid' => $merchantOid]);
    }

    /**
     * Ücretsiz / yerel klinik üyelik aktivasyonu (PayTR callback ile aynı kurulum alanları).
     *
     * @param  array<string, mixed>  $kurulum
     */
    protected function activateClinicMembershipLocal(Doktor $doktor, Paket $paket, string $periyot, array $kurulum, string $ref): void
    {
        $baslangic = now();
        $bitis = $periyot === 'aylik' ? now()->addMonth() : now()->addYear();
        $ilModel = Il::find($kurulum['il_id'] ?? null);
        $ilceModel = Ilce::where('il_id', $ilModel?->id)
            ->where('ad', $kurulum['ilce_id'] ?? '')
            ->first();

        DB::transaction(function () use ($doktor, $paket, $periyot, $kurulum, $ref, $baslangic, $bitis, $ilModel, $ilceModel) {
            $klinik = Klinik::create([
                'ad' => $kurulum['klinik_adi'],
                'sahip_doktor_id' => $doktor->id,
                'paket_id' => $paket->id,
                'telefon' => $kurulum['telefon'] ?? $doktor->telefon,
                'e_posta' => $kurulum['e_posta'] ?? $doktor->e_posta,
                'adres' => $kurulum['adres'] ?? '',
                'il_id' => $ilModel?->id,
                'ilce_id' => $ilceModel?->id,
                'odeme_periyodu' => $periyot,
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis' => $bitis,
                'max_doktor_sayisi' => $paket->max_doktor_sayisi ?? 3,
                'iyzico_subscription_reference_code' => $ref,
                'iyzico_subscription_status' => 'ACTIVE',
                'abonelik_yenileme_kapali' => false,
                'aktif_mi' => true,
            ]);

            $doktor->forceFill([
                'tur' => 'klinik',
                'klinik_id' => $klinik->id,
                'klinik_rolu' => 'sahip',
                'klinik_katilma_tarihi' => now(),
                'klinik_aktif_mi' => true,
                'klinik_adi' => $kurulum['klinik_adi'],
                'paket_id' => $paket->id,
                'odeme_periyodu' => $periyot,
                'uyelik_baslangic' => $baslangic,
                'uyelik_bitis' => $bitis,
                'iyzico_subscription_reference_code' => $ref,
                'iyzico_subscription_status' => 'ACTIVE',
                'abonelik_yenileme_kapali' => false,
                'kayit_paket_id' => null,
                'kayit_periyot' => null,
                'platformda_gorunur' => true,
            ])->save();

            $existingPatients = Hasta::whereHas('randevular', function ($query) use ($doktor) {
                $query->where('doktor_id', $doktor->id);
            })->pluck('id')->toArray();
            if (! empty($existingPatients)) {
                $syncData = [];
                foreach ($existingPatients as $pId) {
                    $syncData[$pId] = [
                        'kayit_tarihi' => now(),
                        'notlar' => 'Bireysel hekimlikten kliniğe geçiş sırasında aktarıldı.',
                    ];
                }
                $klinik->hastalar()->syncWithoutDetaching($syncData);
            }
        });
    }

    /**
     * Show packages selection for logged-in doctor.
     * Her zaman grid gösterilir — kayit_paket_id ile otomatik ödeme kilidi YOK.
     * Öneri: ?oneri=ID (kayıt niyeti / highlight).
     */
    public function paketSecFormu(Request $request)
    {
        $doktorGate = Auth::guard('doktor')->user();
        if ($doktorGate && ! $doktorGate->canProceedToPayment()) {
            return redirect()
                ->route('frontend.hekim.meslek.bekleme')
                ->with('hata', 'Ödeme için önce meslek belgenizin onaylanması gerekir.');
        }

        $doktor = Auth::guard('doktor')->user();
        $doktor->loadMissing('kayitPaketi', 'paket');

        $bekleyenHavaleEarly = UyelikOdeme::bekleyenHavaleForDoktor((int) $doktor->id);
        $sonOnayliEarly = ! $bekleyenHavaleEarly
            ? UyelikOdeme::sonOnayliHavaleForDoktor((int) $doktor->id, 30)
            : null;

        // Havale yeni onaylandı + aktif üyelik: labirent yok; paket değiştirmek isterse grid açık
        // (önceden basarili'ya atılıyordu — paket değişimi engelleniyordu)

        // Get all active packages (yalnızca aktif — id spoof ile pasif paket yok)
        $paketler = Paket::where('aktif_mi', true)->with('sistemOzellikleri')->orderBy('sira')->get();

        // Separate them by type
        $bireyselPaketler = $paketler->where('tur', 'bireysel')->values();
        $klinikPaketler = $paketler->where('tur', 'klinik')->values();
        $maxYillikTasarrufYuzde = $this->maxYillikTasarrufYuzde($paketler);

        $bekleyenHavale = $bekleyenHavaleEarly;
        $sonOnayliHavale = $sonOnayliEarly;

        // Highlight: query oneri > kayit niyeti > aktif paket
        $oneriId = (int) $request->query('oneri', 0);
        if ($oneriId <= 0 && $doktor->hasKayitPaketNiyeti()) {
            $oneriId = (int) $doktor->kayit_paket_id;
        }
        if ($oneriId <= 0 && $doktor->paket_id) {
            $oneriId = (int) $doktor->paket_id;
        }
        // Geçersiz öneri id (pasif/silinmiş) temizle
        if ($oneriId > 0 && ! $paketler->contains('id', $oneriId)) {
            $oneriId = 0;
        }
        $oneriPeriyot = $request->query('periyot', $doktor->kayit_periyot ?: 'aylik');
        if (! in_array($oneriPeriyot, ['aylik', 'yillik'], true)) {
            $oneriPeriyot = 'aylik';
        }

        return view('frontend.hekim.paket_sec', compact(
            'doktor',
            'bireyselPaketler',
            'klinikPaketler',
            'maxYillikTasarrufYuzde',
            'bekleyenHavale',
            'sonOnayliHavale',
            'oneriId',
            'oneriPeriyot',
        ));
    }

    /**
     * Başlangıç paketi 14 gün ücretsiz deneme — ödeme yok.
     */
    public function paketDenemeBaslat(Request $request)
    {
        $doktor = Auth::guard('doktor')->user();
        $paket = Paket::where('aktif_mi', true)->findOrFail($request->input('paket_id'));

        if ($paket->klinikPaketiMi()) {
            return redirect()->route('frontend.hekim.paket_sec')
                ->with('hata', 'Deneme yalnızca bireysel Başlangıç paketi içindir.');
        }

        if (! $paket->denemeVarMi()) {
            return redirect()->route('frontend.hekim.paket_ode', [
                'paket' => $paket->id,
                'periyot' => 'aylik',
            ])->with('hata', 'Bu pakette ücretsiz deneme yok.');
        }

        if (! $doktor->canStartTrial($paket)) {
            return redirect()->route('frontend.hekim.paket_ode', [
                'paket' => $paket->id,
                'periyot' => 'aylik',
            ])->with(
                'hata',
                $doktor->deneme_kullanildi
                    ? 'Ücretsiz deneme hakkınızı daha önce kullandınız. Lütfen ödeme ile devam edin.'
                    : 'Deneme başlatılamıyor. Lütfen ödeme ile paket seçin.'
            );
        }

        $gun = $paket->denemeGun();
        $baslangic = now();
        $bitis = now()->addDays($gun);

        $doktor->update([
            'paket_id' => $paket->id,
            'kayit_paket_id' => null,
            'kayit_periyot' => null,
            'odeme_periyodu' => 'deneme',
            'uyelik_baslangic' => $baslangic,
            'uyelik_bitis' => $bitis,
            'deneme_kullanildi' => true,
            'iyzico_subscription_reference_code' => 'trial_'.$gun.'d_'.Str::random(10),
            'iyzico_subscription_status' => 'TRIAL',
            'tur' => 'bireysel',
            'abonelik_yenileme_kapali' => false,
            'abonelik_iptal_at' => null,
            'abonelik_iptal_nedeni' => null,
            'platformda_gorunur' => true,
        ]);

        MetaPixel::queueOnce(
            'start_trial_'.$doktor->id.'_'.$paket->id,
            'StartTrial',
            array_merge(
                MetaPixel::money(0),
                [
                    'content_name' => $paket->ad,
                    'content_ids' => [(string) $paket->id],
                    'predicted_ltv' => 0,
                ]
            )
        );

        return redirect()
            ->route('frontend.hekim.basarili')
            ->with(
                'basarili',
                "{$gun} günlük ücretsiz denemeniz başladı. Süre bitince paket seçip ödeme yapmanız gerekecek."
            );
    }

    /**
     * Show package payment form for logged-in doctor.
     * Web sitesi paketinde domain seçilmediyse önce domain adımına yönlendir.
     */
    public function paketOdeFormu(Request $request)
    {
        $doktorGate = Auth::guard('doktor')->user();
        if ($doktorGate && ! $doktorGate->canProceedToPayment()) {
            return redirect()
                ->route('frontend.hekim.meslek.bekleme')
                ->with('hata', 'Ödeme adımına geçmeden önce meslek belgenizin onaylanması gerekir.');
        }

        $paketId = (int) $request->query('paket', 0);
        $periyot = $request->query('periyot', 'aylik');
        if (! in_array($periyot, ['aylik', 'yillik'], true)) {
            $periyot = 'aylik';
        }

        // Güvenlik: yalnız aktif paket; query yoksa veya geçersizse grid'e dön (kayit_paket_id ile sessizce starter'a düşme)
        $secilenPaket = $paketId > 0
            ? Paket::where('aktif_mi', true)->with('sistemOzellikleri')->find($paketId)
            : null;
        if (! $secilenPaket) {
            return redirect()
                ->route('frontend.hekim.paket_sec', ['degistir' => 1])
                ->with('hata', 'Lütfen listeden bir paket seçin. Ödeme yalnızca seçtiğiniz pakete yapılır.');
        }

        // Ödeme adımında seçim = kayıt niyetini güncelle (paket değiştiyse)
        $doktor = Auth::guard('doktor')->user();
        if ($doktor && (
            ! $doktor->hasKayitPaketNiyeti()
            || (int) $doktor->kayit_paket_id !== (int) $secilenPaket->id
            || $doktor->kayit_periyot !== $periyot
        )) {
            $doktor->forceFill([
                'kayit_paket_id' => $secilenPaket->id,
                'kayit_periyot' => $periyot,
            ])->save();
        }

        // Deneme hakkı varsa ödeme formuna girmeden deneme başlat sayfası / otomatik
        $doktor = Auth::guard('doktor')->user();
        if (
            $request->query('mod') === 'deneme'
            || ($secilenPaket->denemeVarMi() && $doktor && $doktor->canStartTrial($secilenPaket) && $request->boolean('auto_trial'))
        ) {
            if ($doktor && $doktor->canStartTrial($secilenPaket)) {
                return $this->paketDenemeBaslat(new Request(['paket_id' => $secilenPaket->id]));
            }
        }

        // Domain adımı zorunlu değil ama web paketinde seçim yoksa ve atlanmamışsa domain'e yönlendir
        if (HekimOnboardingController::packageNeedsDomain($secilenPaket)) {
            $pending = session(HekimOnboardingController::SESSION_PENDING);
            $pendingOk = is_array($pending)
                && (int) ($pending['paket_id'] ?? 0) === (int) $secilenPaket->id
                && ! empty($pending['domain']);
            $skipped = (bool) session('onboarding_domain_skipped');

            if (! $pendingOk && ! $skipped && ! $request->boolean('domain_ok')) {
                return redirect()->route('frontend.hekim.onboarding.domain', [
                    'paket' => $secilenPaket->id,
                    'periyot' => $periyot,
                ]);
            }
        }

        $doktor = Auth::guard('doktor')->user();
        $iller = Il::orderBy('ad')->get();
        $driver = app(PaymentDriverService::class);
        $paytrAvailable = $driver->isPaytrActive();
        $iyzicoAvailable = $driver->isIyzicoActive();
        $paymentSettings = SiteAyari::query()->first();
        $bankAvailable = $driver->isHavaleActive();
        $listedPrice = $periyot === 'aylik'
            ? (float) $secilenPaket->aylik_fiyat
            : (float) $secilenPaket->yillik_fiyat;
        $discountedPrice = $periyot === 'aylik'
            ? $secilenPaket->aylik_indirimli_fiyat
            : $secilenPaket->yillik_indirimli_fiyat;
        $tutar = $discountedPrice !== null && (float) $discountedPrice > 0
            ? (float) $discountedPrice
            : $listedPrice;

        $refFiyat = app(ReferansService::class)->indirimliTutar($doktor, $tutar);
        $tutarBrut = $refFiyat['brut'];
        $tutar = $refFiyat['tutar'];
        $referansIndirim = $refFiyat;

        $pendingDomain = session(HekimOnboardingController::SESSION_PENDING);

        $bekleyenHavale = $doktor
            ? UyelikOdeme::bekleyenHavaleForDoktor((int) $doktor->id)
            : null;
        $sonOnayliHavale = ($doktor && ! $bekleyenHavale)
            ? UyelikOdeme::sonOnayliHavaleForDoktor((int) $doktor->id, 30)
            : null;

        // Aktif üyelik + onaylı havale: ödeme formunda “ne oldu?” belirsizliği olmasın
        if (
            $doktor
            && ! $bekleyenHavale
            && $doktor->hasActiveMembership()
            && $sonOnayliHavale
            && ! $request->boolean('degistir')
        ) {
            return redirect()
                ->route('frontend.hekim.basarili')
                ->with(
                    'basarili',
                    'Havale ödemeniz onaylandı. Üyeliğiniz aktif — ödeme tekrarı gerekmez.'
                );
        }

        $showAktifUyelikKart = $doktor && $doktor->hasActiveMembership() && ! $bekleyenHavale && ! $sonOnayliHavale;

        MetaPixel::queue('InitiateCheckout', array_merge(
            MetaPixel::money((float) $tutar),
            [
                'content_name' => $secilenPaket->ad,
                'content_ids' => [(string) $secilenPaket->id],
                'content_type' => 'product',
                'num_items' => 1,
            ]
        ));

        return view('frontend.hekim.paket_ode', compact(
            'secilenPaket',
            'periyot',
            'doktor',
            'iller',
            'paytrAvailable',
            'iyzicoAvailable',
            'paymentSettings',
            'bankAvailable',
            'tutar',
            'tutarBrut',
            'referansIndirim',
            'pendingDomain',
            'bekleyenHavale',
            'sonOnayliHavale',
            'showAktifUyelikKart',
        ));
    }

    /**
     * Handle package payment and subscription activation.
     */
    public function paketOde(Request $request)
    {
        $doktorGate = Auth::guard('doktor')->user();
        if ($doktorGate && ! $doktorGate->canProceedToPayment()) {
            return redirect()
                ->route('frontend.hekim.meslek.bekleme')
                ->with('hata', 'Meslek belgesi onaylanmadan ödeme yapılamaz.');
        }

        // POST paket_id — yalnız aktif; gizli id ile pasif/ücretsiz spoof engeli
        $paket = Paket::where('aktif_mi', true)->find($request->input('paket_id'));
        if (! $paket) {
            return redirect()
                ->route('frontend.hekim.paket_sec', ['degistir' => 1])
                ->with('hata', 'Geçersiz paket. Lütfen listeden yeniden seçin.');
        }
        $periodPrice = $request->odeme_periyodu === 'yillik'
            ? (float) $paket->yillik_fiyat
            : (float) $paket->aylik_fiyat;
        $discountedPrice = $request->odeme_periyodu === 'yillik'
            ? $paket->yillik_indirimli_fiyat
            : $paket->aylik_indirimli_fiyat;
        $tutar = $discountedPrice !== null && (float) $discountedPrice > 0
            ? (float) $discountedPrice
            : $periodPrice;
        $doktor = Auth::guard('doktor')->user();
        $refFiyat = app(ReferansService::class)->indirimliTutar($doktor, $tutar);
        $tutarBrut = $refFiyat['brut'];
        $tutar = $refFiyat['tutar'];
        $isFree = $tutar <= 0;

        $rules = [
            'paket_id' => 'required|exists:paketler,id',
            'odeme_periyodu' => 'required|in:aylik,yillik',
            'mesafeli_onay' => 'accepted',
            'kvkk_odeme_onay' => 'accepted',
        ];

        // If it is a clinic package, we require clinic details
        if ($paket->klinikPaketiMi()) {
            $rules['klinik_adi'] = 'required|string|max:255';
            $rules['telefon'] = 'required|string';
            $rules['e_posta'] = 'nullable|email|max:255';
            $rules['adres'] = 'required|string';
            $rules['il_id'] = 'required|exists:iller,id';
            $rules['ilce_id'] = 'required|string|max:255';
        }

        // Ücretli ödemede fatura bilgileri zorunlu (kayıtta değil, burada)
        if (! $isFree) {
            $rules['fatura_tipi'] = 'required|in:bireysel,kurumsal';
            $rules['fatura_unvan'] = 'required|string|max:255';
            $rules['fatura_tc_vkn'] = ['required', 'string', 'regex:/^[0-9]{10,11}$/'];
            $rules['fatura_adres'] = 'required|string|max:1000';
            $rules['fatura_il'] = 'required|string|max:80';
            $rules['fatura_ilce'] = 'required|string|max:80';
            $rules['fatura_posta_kodu'] = 'nullable|string|max:10';
            $rules['fatura_email'] = 'required|email|max:190';
            $rules['fatura_telefon'] = 'required|string|max:40';
            $rules['fatura_vergi_dairesi'] = $request->input('fatura_tipi') === 'kurumsal'
                ? 'required|string|max:120'
                : 'nullable|string|max:120';
        }

        $driver = app(PaymentDriverService::class);
        $allowedMethods = $driver->availableMethods();

        if (! $isFree) {
            if ($allowedMethods === []) {
                return back()->withInput()->withErrors([
                    'odeme_yontemi' => 'Şu an açık bir ödeme kanalı yok. Yönetici panelinden PayTR, iyzico veya havale açılmalıdır.',
                ]);
            }
            $rules['odeme_yontemi'] = 'required|in:'.implode(',', $allowedMethods);
            // PayTR Direkt API 3D — kart alanları
            if ($request->input('odeme_yontemi', 'paytr') === 'paytr' && in_array('paytr', $allowedMethods, true)) {
                $rules['kart_sahibi'] = 'required|string|max:100';
                $rules['kart_no'] = 'required|string|min:15|max:19';
                $rules['kart_ay'] = 'required|string|min:1|max:2';
                $rules['kart_yil'] = 'required|string|min:2|max:4';
                $rules['kart_cvv'] = 'required|string|min:3|max:4';
            }
            if ($request->input('odeme_yontemi') === 'iyzico' && in_array('iyzico', $allowedMethods, true)) {
                $rules['kart_sahibi'] = 'required|string|max:100';
                $rules['kart_no'] = 'required|string|min:15|max:19';
                $rules['kart_ay'] = 'required|string|min:1|max:2';
                $rules['kart_yil'] = 'required|string|min:2|max:4';
                $rules['kart_cvv'] = 'required|string|min:3|max:4';
            }
        }

        $request->validate($rules, [
            'paket_id.exists' => 'Lütfen geçerli bir üyelik paketi seçin.',
            'odeme_periyodu.in' => 'Ödeme periyodu aylık veya yıllık olmalıdır.',
            'klinik_adi.required' => 'Klinik adı zorunludur.',
            'telefon.required' => 'Klinik telefon numarası zorunludur.',
            'adres.required' => 'Klinik adresi zorunludur.',
            'il_id.required' => 'İl seçimi zorunludur.',
            'ilce_id.required' => 'İlçe seçimi zorunludur.',
            'mesafeli_onay.accepted' => 'Mesafeli satış sözleşmesini kabul etmelisiniz.',
            'kvkk_odeme_onay.accepted' => 'KVKK aydınlatma metnini kabul etmelisiniz.',
            'odeme_yontemi.in' => 'Seçilen ödeme yöntemi şu an kullanılamıyor.',
            'fatura_tipi.required' => 'Fatura tipi seçin (bireysel veya kurumsal).',
            'fatura_unvan.required' => 'Fatura unvanı / ad soyad zorunludur.',
            'fatura_tc_vkn.required' => 'T.C. kimlik veya vergi numarası zorunludur.',
            'fatura_tc_vkn.regex' => 'T.C. 11 hane veya vergi no 10 hane olmalıdır.',
            'fatura_adres.required' => 'Fatura adresi zorunludur.',
            'fatura_il.required' => 'Fatura ili zorunludur.',
            'fatura_ilce.required' => 'Fatura ilçesi zorunludur.',
            'fatura_email.required' => 'Fatura e-postası zorunludur.',
            'fatura_telefon.required' => 'Fatura telefonu zorunludur.',
            'fatura_vergi_dairesi.required' => 'Kurumsal faturada vergi dairesi zorunludur.',
        ]);

        // TC / VKN hane kontrolü
        $faturaBilgisi = null;
        if (! $isFree) {
            $faturaBilgisi = Doktor::normalizeFaturaFromRequest($request);
            $digits = $faturaBilgisi['tc_vkn'];
            if ($faturaBilgisi['tip'] === 'bireysel' && strlen($digits) !== 11) {
                return back()->withInput()->withErrors(['fatura_tc_vkn' => 'Bireysel faturada T.C. kimlik 11 haneli olmalıdır.']);
            }
            if ($faturaBilgisi['tip'] === 'kurumsal' && strlen($digits) !== 10) {
                return back()->withInput()->withErrors(['fatura_tc_vkn' => 'Kurumsal faturada vergi no 10 haneli olmalıdır.']);
            }
            // Prefill için hekim kaydına yaz
            $doktor->saveFaturaBilgileri($faturaBilgisi);
        }

        $defaultMethod = $allowedMethods[0] ?? 'havale';
        $odemeYontemi = $request->input('odeme_yontemi', $defaultMethod);

        $paymentSettings = SiteAyari::query()->first();

        if ($isFree) {
            $paymentResult = [
                'status' => 'success',
                'referenceCode' => 'free_trial_'.Str::random(12),
                'subscriptionStatus' => 'ACTIVE',
            ];
        } elseif ($odemeYontemi === 'havale') {
            if (! $driver->isHavaleActive()) {
                return back()->withInput()->withErrors(['odeme_yontemi' => 'Havale kanalı kapalı veya banka bilgileri yapılandırılmamış.']);
            }

            $request->validate([
                'havale_referans' => ['required', 'string', 'max:100'],
            ], [
                'havale_referans.required' => 'Havale referansını veya açıklamasını girin.',
            ]);

            $kurulumHavale = $paket->klinikPaketiMi() ? $request->only([
                'klinik_adi', 'telefon', 'e_posta', 'adres', 'il_id', 'ilce_id',
            ]) : [];
            $kurulumHavale['tutar_brut'] = $tutarBrut;
            $kurulumHavale['referans_indirim_yuzde'] = $refFiyat['indirim_yuzde'];
            if ($faturaBilgisi) {
                $kurulumHavale['fatura'] = $faturaBilgisi;
            }

            // Aynı paket için zaten bekleyen havale varsa yeni satır açma — hekime durumu göster
            $mevcutBekleyen = UyelikOdeme::query()
                ->havale()
                ->beklemede()
                ->where('doktor_id', $doktor->id)
                ->where('paket_id', $paket->id)
                ->latest('id')
                ->first();

            if ($mevcutBekleyen) {
                $mevcutBekleyen->update([
                    'havale_referans' => trim((string) $request->havale_referans) ?: $mevcutBekleyen->havale_referans,
                    'tutar' => $tutar,
                    'odeme_periyodu' => $request->odeme_periyodu,
                    'kurulum_verisi' => $kurulumHavale ?: $mevcutBekleyen->kurulum_verisi,
                    'fatura_bilgisi' => $faturaBilgisi ?: $mevcutBekleyen->fatura_bilgisi,
                ]);
            } else {
                UyelikOdeme::create([
                    'doktor_id' => $doktor->id,
                    'paket_id' => $paket->id,
                    'odeme_yontemi' => 'havale',
                    'provider' => 'banka',
                    'odeme_periyodu' => $request->odeme_periyodu,
                    'tutar' => $tutar,
                    'durum' => 'beklemede',
                    'havale_referans' => trim((string) $request->havale_referans),
                    'kurulum_verisi' => $kurulumHavale ?: null,
                    'fatura_bilgisi' => $faturaBilgisi,
                ]);
            }

            // paket_sec kayit niyetiyle tekrar odeme sayfasina atmasin; flash kaybolmasin
            return redirect()
                ->route('frontend.hekim.paket_ode', [
                    'paket' => $paket->id,
                    'periyot' => $request->odeme_periyodu,
                ])
                ->with(
                    'basarili',
                    'Havale bildiriminiz alındı ve kaydedildi. Yönetici banka hareketini onaylayınca üyeliğiniz otomatik açılır. Aşağıda bildiriminizin durumunu görebilirsiniz.'
                );
        } else {
            // Kartlı ödeme — hekimin seçtiği kanal (paytr | iyzico)
            $kurulumKart = $paket->klinikPaketiMi() ? $request->only([
                'klinik_adi', 'telefon', 'e_posta', 'adres', 'il_id', 'ilce_id',
            ]) : [];
            if ($faturaBilgisi) {
                $kurulumKart['fatura'] = $faturaBilgisi;
            }

            // PayTR Direkt API ve iyzico için kart alanları (3D / abonelik)
            $kartBilgileri = [];
            if (in_array($odemeYontemi, ['iyzico', 'paytr'], true)) {
                $ay = str_pad(preg_replace('/\D/', '', (string) $request->input('kart_ay', '')), 2, '0', STR_PAD_LEFT);
                $yil = preg_replace('/\D/', '', (string) $request->input('kart_yil', ''));
                if (strlen($yil) === 4) {
                    $yil = substr($yil, -2);
                }
                $kartBilgileri = [
                    'kart_sahibi' => trim((string) $request->input('kart_sahibi', '')),
                    'kart_no' => preg_replace('/\D/', '', (string) $request->input('kart_no', '')),
                    'kart_cvv' => preg_replace('/\D/', '', (string) $request->input('kart_cvv', '')),
                    'kart_ay' => $ay,
                    'kart_yil' => $yil,
                    'kart_skt' => $ay.'/'.$yil,
                ];
            }

            return $driver->startCheckout(
                $doktor,
                $paket,
                $request->odeme_periyodu,
                $tutarBrut,
                $kurulumKart,
                $request,
                $kartBilgileri,
                $odemeYontemi === 'iyzico' ? 'iyzico' : 'paytr'
            );
        }

        // Calculate membership dates
        $baslangic = now();
        $bitis = $request->odeme_periyodu === 'aylik' ? now()->addMonth() : now()->addYear();

        DB::transaction(function () use ($request, $paket, $baslangic, $bitis, $paymentResult, $doktor) {
            if ($paket->klinikPaketiMi()) {
                $ilModel = Il::find($request->il_id);
                $ilceModel = Ilce::where('il_id', $ilModel?->id)->where('ad', $request->ilce_id)->first();

                // Create the Clinic
                $klinik = Klinik::create([
                    'ad' => $request->klinik_adi,
                    'sahip_doktor_id' => $doktor->id,
                    'paket_id' => $paket->id,
                    'telefon' => $request->telefon,
                    'e_posta' => $request->e_posta,
                    'adres' => $request->adres,
                    'il_id' => $ilModel?->id,
                    'ilce_id' => $ilceModel?->id,
                    'odeme_periyodu' => $request->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'max_doktor_sayisi' => $paket->max_doktor_sayisi ?? 3,
                    'aktif_mi' => true,
                ]);

                // Update the Doctor
                $doktor->update([
                    'paket_id' => $paket->id,
                    'kayit_paket_id' => null,
                    'kayit_periyot' => null,
                    'odeme_periyodu' => $request->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_reference_code' => $paymentResult['referenceCode'],
                    'iyzico_subscription_status' => $paymentResult['subscriptionStatus'],
                    'klinik_id' => $klinik->id,
                    'klinik_rolu' => 'sahip',
                    'klinik_katilma_tarihi' => now(),
                    'klinik_aktif_mi' => true,
                    'tur' => 'klinik',
                    'platformda_gorunur' => true,
                ]);
            } else {
                // Individual package (ücretli — deneme bittikten sonra da buraya düşer)
                $doktor->update([
                    'paket_id' => $paket->id,
                    'kayit_paket_id' => null,
                    'kayit_periyot' => null,
                    'odeme_periyodu' => $request->odeme_periyodu,
                    'uyelik_baslangic' => $baslangic,
                    'uyelik_bitis' => $bitis,
                    'iyzico_subscription_reference_code' => $paymentResult['referenceCode'],
                    'iyzico_subscription_status' => $paymentResult['subscriptionStatus'],
                    'tur' => 'bireysel',
                    // Yeni ödeme = iptal bayraklarını temizle
                    'abonelik_yenileme_kapali' => false,
                    'abonelik_iptal_at' => null,
                    'abonelik_iptal_nedeni' => null,
                    'platformda_gorunur' => true,
                ]);
            }
        });

        // Ücretsiz paket aktivasyonu
        MetaPixel::queueOnce(
            'purchase_free_'.$doktor->id.'_'.$paket->id.'_'.$request->odeme_periyodu,
            'Purchase',
            array_merge(
                MetaPixel::money(0),
                [
                    'content_name' => $paket->ad,
                    'content_ids' => [(string) $paket->id],
                    'content_type' => 'product',
                    'num_items' => 1,
                ]
            )
        );
        MetaPixel::queueOnce(
            'subscribe_free_'.$doktor->id.'_'.$paket->id.'_'.$request->odeme_periyodu,
            'Subscribe',
            array_merge(
                MetaPixel::money(0),
                [
                    'content_name' => $paket->ad,
                    'content_ids' => [(string) $paket->id],
                    'predicted_ltv' => 0,
                ]
            )
        );

        return $this->redirectAfterMembership($doktor->fresh());
    }
}
