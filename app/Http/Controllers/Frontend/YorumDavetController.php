<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Yorum;
use App\Models\YorumDaveti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Girişsiz yorum bırakma (e-postadaki tek kullanımlık kısa bağlantı).
 *
 * Yetkilendirme oturumdan değil TOKEN'dan gelir: token, randevu + hasta
 * ikilisini kanıtlar. Bu yüzden istekten gelen hiçbir kimlik alanına
 * (hasta_id, randevu_id ...) güvenilmez; hepsi davet kaydından okunur.
 */
class YorumDavetController extends Controller
{
    /** Değerlendirme formu. */
    public function form(string $token)
    {
        $davet = YorumDaveti::bul($token);

        if (! $davet) {
            return $this->hataSayfasi('Bağlantı geçersiz', 'Bu değerlendirme bağlantısı tanınmadı. E-postadaki adresi eksiksiz kopyaladığınızdan emin olun.');
        }

        if ($davet->kullanildi_at !== null) {
            return $this->hataSayfasi('Değerlendirmeniz alınmış', 'Bu bağlantı ile daha önce değerlendirme yaptınız. Teşekkür ederiz.');
        }

        if (! $davet->kullanilabilir()) {
            return $this->hataSayfasi('Bağlantının süresi doldu', 'Değerlendirme bağlantıları '.YorumDaveti::GECERLILIK_GUN.' gün geçerlidir.');
        }

        $davet->loadMissing(['doktor', 'randevu']);

        return view('frontend.yorum.davet', [
            'token' => $token,
            'davet' => $davet,
            'doktor' => $davet->doktor,
            'randevu' => $davet->randevu,
        ]);
    }

    /** Değerlendirmeyi kaydeder. */
    public function kaydet(Request $request, string $token)
    {
        $davet = YorumDaveti::bul($token);

        if (! $davet || ! $davet->kullanilabilir()) {
            return $this->hataSayfasi(
                'Bağlantı kullanılamıyor',
                'Bu değerlendirme bağlantısı geçersiz, kullanılmış veya süresi dolmuş.'
            );
        }

        $validated = $request->validate([
            'puan' => ['required', 'integer', 'min:1', 'max:5'],
            'yorum' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'puan.required' => 'Lütfen bir puan seçin.',
            'puan.min' => 'Puan 1 ile 5 arasında olmalıdır.',
            'puan.max' => 'Puan 1 ile 5 arasında olmalıdır.',
            'yorum.required' => 'Lütfen kısa bir değerlendirme yazın.',
            'yorum.min' => 'Değerlendirmeniz en az 10 karakter olmalıdır.',
            'yorum.max' => 'Değerlendirmeniz en fazla 1000 karakter olabilir.',
        ]);

        // Token'ı işaretlemek ile yorumu yazmak tek işlemde: ikisi birden
        // olur ya da hiçbiri. Aksi halde çift gönderim mükerrer yorum üretir.
        DB::transaction(function () use ($davet, $validated) {
            $kilitli = YorumDaveti::query()->lockForUpdate()->find($davet->id);
            if (! $kilitli || ! $kilitli->kullanilabilir()) {
                return;
            }

            Yorum::firstOrCreate(
                ['hasta_id' => $kilitli->hasta_id, 'randevu_id' => $kilitli->randevu_id],
                [
                    'doktor_id' => $kilitli->doktor_id,
                    'puan' => $validated['puan'],
                    'yorum' => $validated['yorum'],
                    'onay_durumu' => 'beklemede',
                ]
            );

            $kilitli->update(['kullanildi_at' => now()]);
        });

        return view('frontend.yorum.tesekkur', [
            'doktor' => $davet->loadMissing('doktor')->doktor,
        ]);
    }

    private function hataSayfasi(string $baslik, string $mesaj)
    {
        return response()->view('frontend.yorum.hata', [
            'baslik' => $baslik,
            'mesaj' => $mesaj,
        ], 410);
    }
}
