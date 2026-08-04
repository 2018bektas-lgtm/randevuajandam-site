<?php

namespace App\Http\Controllers;

use App\Http\Requests\Yonetim\PaketStoreRequest;
use App\Models\Doktor;
use App\Models\DomainOrder;
use App\Models\Klinik;
use App\Models\Paket;
use App\Models\PaketOzelligi;
use App\Models\UyelikOdeme;
use App\Models\Yonetici;
use App\Support\PaketOzellikKatalogu;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PaketController extends Controller
{
    public function index()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $paketler = Paket::with('sistemOzellikleri')->orderBy('tur')->orderBy('sira')->orderBy('id')->get();

        return view('yonetim.paketler.index', compact('yonetici', 'paketler'));
    }

    public function create()
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $ozellikGruplari = PaketOzellikKatalogu::gruplu();
        $seciliOzellikler = old('sistem_ozellikleri', []);

        return view('yonetim.paketler.ekle', compact('yonetici', 'ozellikGruplari', 'seciliOzellikler'));
    }

    public function store(PaketStoreRequest $request)
    {
        $payload = $this->payloadFromRequest($request);
        $kodlar = $request->input('sistem_ozellikleri', []) ?: [];

        $payload['ozellikler'] = PaketOzellikKatalogu::vitrinMetinleri(
            $kodlar,
            $payload['sms_aylik_kontor'] ?? null,
            $payload['max_randevu_sayisi'] ?? null,
            $payload['max_hasta_sayisi'] ?? null
        );

        $paket = Paket::create($payload);
        $this->syncOzellikler($paket, $kodlar);

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket başarıyla oluşturuldu.');
    }

    public function edit($id)
    {
        /** @var Yonetici $yonetici */
        $yonetici = Auth::guard('yonetici')->user();
        $paket = Paket::with('sistemOzellikleri')->findOrFail($id);
        $ozellikGruplari = PaketOzellikKatalogu::gruplu();
        $seciliOzellikler = old(
            'sistem_ozellikleri',
            $paket->sistemOzellikleri->pluck('kod')->all()
        );

        return view('yonetim.paketler.duzenle', compact('yonetici', 'paket', 'ozellikGruplari', 'seciliOzellikler'));
    }

    public function update(PaketStoreRequest $request, $id)
    {
        $paket = Paket::findOrFail($id);
        $payload = $this->payloadFromRequest($request);
        $kodlar = $request->input('sistem_ozellikleri', []) ?: [];

        $payload['ozellikler'] = PaketOzellikKatalogu::vitrinMetinleri(
            $kodlar,
            $payload['sms_aylik_kontor'] ?? null,
            $payload['max_randevu_sayisi'] ?? null,
            $payload['max_hasta_sayisi'] ?? null
        );

        $paket->update($payload);
        $this->syncOzellikler($paket, $kodlar);

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket başarıyla güncellendi.');
    }

    public function destroy($id)
    {
        $paket = Paket::findOrFail($id);

        $engeller = [];

        $doktorSayisi = Doktor::where('paket_id', $paket->id)->count();
        if ($doktorSayisi > 0) {
            $engeller[] = "{$doktorSayisi} doktor bu pakete bağlı";
        }

        $kayitNiyet = Doktor::where('kayit_paket_id', $paket->id)->count();
        if ($kayitNiyet > 0) {
            $engeller[] = "{$kayitNiyet} doktor kayıt sırasında bu paketi seçmiş";
        }

        $klinikSayisi = Klinik::where('paket_id', $paket->id)->count();
        if ($klinikSayisi > 0) {
            $engeller[] = "{$klinikSayisi} klinik bu pakete bağlı";
        }

        $odemeSayisi = UyelikOdeme::where('paket_id', $paket->id)->count();
        if ($odemeSayisi > 0) {
            $engeller[] = "{$odemeSayisi} üyelik ödemesi kaydı var (geçmiş silinemez)";
        }

        if (Schema::hasTable('domain_orders')) {
            $domainSayisi = DomainOrder::where('paket_id', $paket->id)->count();
            if ($domainSayisi > 0) {
                $engeller[] = "{$domainSayisi} domain siparişi bu pakete bağlı";
            }
        }

        if ($engeller !== []) {
            return back()->withErrors([
                'hata' => 'Bu paket silinemez: '.implode('; ', $engeller)
                    .'. Ödeme ve üyelik geçmişi korunur. Satışta göstermemek için paketi «Pasif» yapın.',
            ]);
        }

        try {
            $paket->delete();
        } catch (QueryException $e) {
            report($e);

            return back()->withErrors([
                'hata' => 'Paket silinemedi: veritabanında bağlı kayıtlar var. Paketi pasife almanız yeterli.',
            ]);
        }

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket başarıyla silindi.');
    }

    public function toggleDurum($id)
    {
        $paket = Paket::findOrFail($id);
        $paket->update([
            'aktif_mi' => ! $paket->aktif_mi,
        ]);

        return redirect()->route('yonetim.paketler.index')->with('basarili', 'Paket durumu güncellendi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(PaketStoreRequest $request): array
    {
        $tur = $request->input('tur');
        $isKlinik = $tur === 'klinik';

        $nullableInt = function (string $key) use ($request): ?int {
            if (! $request->filled($key)) {
                return null;
            }
            $v = (int) $request->input($key);

            return $v > 0 ? $v : null;
        };

        return [
            'ad' => $request->ad,
            'tur' => $tur,
            'aciklama' => $request->aciklama,
            'aylik_fiyat' => $request->aylik_fiyat,
            'aylik_indirimli_fiyat' => $request->aylik_indirimli_fiyat,
            'yillik_fiyat' => $request->yillik_fiyat,
            'yillik_indirimli_fiyat' => $request->yillik_indirimli_fiyat,
            'ek_doktor_aylik_fiyat' => $isKlinik ? $request->ek_doktor_aylik_fiyat : null,
            'ek_doktor_yillik_fiyat' => $isKlinik ? $request->ek_doktor_yillik_fiyat : null,
            'ek_personel_aylik_fiyat' => $request->ek_personel_aylik_fiyat,
            'ek_personel_yillik_fiyat' => $request->ek_personel_yillik_fiyat,
            'aktif_mi' => $request->boolean('aktif_mi'),
            'max_doktor_sayisi' => $isKlinik ? $nullableInt('max_doktor_sayisi') : null,
            'max_ek_doktor' => $isKlinik ? $nullableInt('max_ek_doktor') : null,
            'max_personel_sayisi' => $nullableInt('max_personel_sayisi'),
            'max_hasta_sayisi' => $nullableInt('max_hasta_sayisi'),
            'max_randevu_sayisi' => $nullableInt('max_randevu_sayisi'),
            'max_hizmet_sayisi' => $nullableInt('max_hizmet_sayisi'),
            'max_biyografi_karakter' => $nullableInt('max_biyografi_karakter'),
            'max_profil_foto' => $nullableInt('max_profil_foto'),
            'sms_aylik_kontor' => $nullableInt('sms_aylik_kontor'),
            'listeleme_oncelik' => (int) ($request->input('listeleme_oncelik') ?? 1),
            'merkezi_finans_mi' => $isKlinik && $request->boolean('merkezi_finans_mi'),
            'toplu_randevu_mi' => $isKlinik && $request->boolean('toplu_randevu_mi'),
            'raporlama_mi' => $isKlinik && $request->boolean('raporlama_mi'),
            'hasta_havuzu_mi' => $isKlinik && $request->boolean('hasta_havuzu_mi'),
            'sira' => (int) ($request->sira ?? 0),
            'one_cikan_mi' => $request->boolean('one_cikan_mi'),
            'etiket' => $request->filled('etiket') ? trim((string) $request->etiket) : null,
            'etiket_stil' => $request->input('etiket_stil') ?: null,
            'deneme_gun' => $request->filled('deneme_gun') ? (int) $request->deneme_gun : null,
            'domain_dahil_mi' => $request->boolean('domain_dahil_mi'),
            'domain_dahil_yil' => (int) ($request->input('domain_dahil_yil') ?: 1),
            'domain_dahil_tlds' => $request->filled('domain_dahil_tlds')
                ? array_values(array_filter(array_map('trim', explode(',', (string) $request->domain_dahil_tlds))))
                : null,
            'iyzico_plan_aylik' => $request->input('iyzico_plan_aylik') ?: null,
            'iyzico_plan_yillik' => $request->input('iyzico_plan_yillik') ?: null,
        ];
    }

    /**
     * @param  list<string>  $kodlar
     */
    private function syncOzellikler(Paket $paket, array $kodlar): void
    {
        $ids = PaketOzelligi::query()->whereIn('kod', $kodlar)->pluck('id')->all();
        $paket->sistemOzellikleri()->sync($ids);
    }
}
