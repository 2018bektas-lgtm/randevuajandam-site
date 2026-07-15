<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\KlinikPersonel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PersonelAuthController extends Controller
{
    /**
     * Show the staff login page.
     */
    public function girisFormu()
    {
        if (Auth::guard('personel')->check()) {
            return redirect()->route('personel.panel');
        }

        return view('personel.giris');
    }

    /**
     * Handle staff login.
     */
    public function girisYap(Request $request)
    {
        $request->validate([
            'e_posta' => 'required|email',
            'sifre' => 'required|string',
        ], [
            'e_posta.required' => 'E-posta adresi gereklidir.',
            'e_posta.email' => 'Lütfen geçerli bir e-posta girin.',
            'sifre.required' => 'Şifre alanı gereklidir.',
        ]);

        $credentials = [
            'e_posta' => $request->e_posta,
            'password' => $request->sifre,
        ];

        // Check if the user exists and is active
        $personel = KlinikPersonel::where('e_posta', $request->e_posta)->first();
        if ($personel && ! $personel->aktif_mi) {
            return back()->withInput()->withErrors([
                'e_posta' => 'Hesabınız pasif duruma getirilmiştir. Lütfen klinik yöneticisi ile iletişime geçin.',
            ]);
        }

        if (Auth::guard('personel')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::guard('personel')->user();
            if (! $user->sifre_degistirildi_mi) {
                return redirect()->route('personel.sifre-degistir');
            }

            return redirect()->route('personel.panel')->with('basari', 'Başarıyla giriş yaptınız.');
        }

        return back()->withInput()->withErrors([
            'e_posta' => 'Girdiğiniz bilgiler sistemdekilerle eşleşmiyor.',
        ]);
    }

    /**
     * Show the password change page.
     */
    public function sifreFormu()
    {
        $personel = Auth::guard('personel')->user();
        if ($personel->sifre_degistirildi_mi) {
            return redirect()->route('personel.panel');
        }

        return view('personel.sifre');
    }

    /**
     * Handle password change.
     */
    public function sifreGuncelle(Request $request)
    {
        $request->validate([
            'sifre' => [
                'required',
                'string',
                'min:8',
                'regex:~^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>_\-#\[\]\\\/]).+$~',
                'confirmed',
            ],
        ], [
            'sifre.required' => 'Yeni şifre alanı zorunludur.',
            'sifre.min' => 'Şifreniz en az 8 karakter olmalıdır.',
            'sifre.regex' => 'Şifreniz en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.',
            'sifre.confirmed' => 'Şifre onayınız uyuşmuyor.',
        ]);

        $personel = KlinikPersonel::findOrFail(Auth::guard('personel')->id());
        $personel->update([
            'sifre' => Hash::make($request->sifre),
            'sifre_degistirildi_mi' => true,
        ]);

        return redirect()->route('personel.panel')->with('basari', 'Şifreniz başarıyla güncellendi. Artık paneli kullanabilirsiniz.');
    }

    /**
     * Log the staff member out.
     */
    public function cikisYap(Request $request)
    {
        Auth::guard('personel')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('personel.giris')->with('basari', 'Oturum kapatıldı.');
    }

    /**
     * Display the staff panel dashboard.
     */
    public function panel()
    {
        $personel = Auth::guard('personel')->user();
        $klinik = $personel->klinik;

        return view('personel.panel', compact('personel', 'klinik'));
    }
}
