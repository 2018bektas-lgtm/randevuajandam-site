<?php

namespace App\Http\Controllers;

use App\Models\Doktor;
use App\Models\Hasta;
use App\Models\Yonetici;
use App\Notifications\SifreSifirlamaLinkBildirimi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the form to request a password reset link.
     */
    public function showLinkRequestForm(Request $request)
    {
        $type = $request->query('type', 'hekim');
        if (!in_array($type, ['hekim', 'hasta', 'yonetici'])) {
            $type = 'hekim';
        }

        return view('auth.sifremi_unuttum', compact('type'));
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'e_posta' => 'required|email',
            'type' => 'required|in:hekim,hasta,yonetici',
        ]);

        $email = $request->e_posta;
        $type = $request->type;

        // Find the user depending on type
        $user = null;
        if ($type === 'hekim') {
            $user = Doktor::where('e_posta', $email)->first();
        } elseif ($type === 'hasta') {
            $user = Hasta::where('e_posta', $email)->first();
        } elseif ($type === 'yonetici') {
            $user = Yonetici::where('e_posta', $email)->first();
        }

        if (!$user) {
            return back()->withErrors(['e_posta' => 'Bu e-posta adresiyle kayıtlı bir hesap bulunamadı.'])->withInput();
        }

        // Generate token
        $token = Str::random(60);

        // Delete any existing tokens for this email in password_reset_tokens
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        // Save to password_reset_tokens
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token), // store hashed token
            'created_at' => now(),
        ]);

        // Send email
        $user->notify(new SifreSifirlamaLinkBildirimi($token, $type));

        return back()->with('basarili', 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi. Lütfen gelen kutunuzu kontrol edin.');
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, $token)
    {
        $type = $request->query('type', 'hekim');
        if (!in_array($type, ['hekim', 'hasta', 'yonetici'])) {
            $type = 'hekim';
        }
        $email = $request->query('email');

        return view('auth.sifre_sifirla', compact('token', 'type', 'email'));
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'type' => 'required|in:hekim,hasta,yonetici',
            'e_posta' => 'required|email',
            'sifre' => 'required|string|min:8|confirmed',
        ], [
            'sifre.required' => 'Şifre alanı zorunludur.',
            'sifre.min' => 'Şifre en az 8 karakter olmalıdır.',
            'sifre.confirmed' => 'Şifre tekrarı uyuşmuyor.',
        ]);

        $email = $request->e_posta;
        $type = $request->type;
        $token = $request->token;

        // Retrieve the token record from db
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return back()->withErrors(['e_posta' => 'Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı.'])->withInput();
        }

        // Check token expiration (60 minutes)
        if (now()->subMinutes(60)->gt($record->created_at)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withErrors(['e_posta' => 'Şifre sıfırlama bağlantısının süresi dolmuş. Lütfen yeni bir talep oluşturun.'])->withInput();
        }

        // Update user password
        $user = null;
        $redirectRoute = '';
        if ($type === 'hekim') {
            $user = Doktor::where('e_posta', $email)->first();
            $redirectRoute = 'frontend.hekim.giris';
        } elseif ($type === 'hasta') {
            $user = Hasta::where('e_posta', $email)->first();
            $redirectRoute = 'frontend.hasta.giris';
        } elseif ($type === 'yonetici') {
            $user = Yonetici::where('e_posta', $email)->first();
            $redirectRoute = 'yonetim.giris';
        }

        if (!$user) {
            return back()->withErrors(['e_posta' => 'Kullanıcı bulunamadı.'])->withInput();
        }

        $user->update([
            'sifre' => Hash::make($request->sifre),
        ]);

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route($redirectRoute)->with('basarili', 'Şifreniz başarıyla sıfırlandı. Yeni şifrenizle giriş yapabilirsiniz.');
    }
}
