<?php

namespace Tests\Feature;

use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private Doktor $doktor;

    private TwoFactorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $il = Il::create(['ad' => 'Ankara', 'plaka' => '06']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Cankaya']);

        $this->doktor = Doktor::create([
            'ad_soyad' => '2FA Hekim',
            'e_posta' => '2fa-hekim@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '05551110000',
            'tur' => 'bireysel',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'aktif_mi' => true,
        ]);

        $this->service = app(TwoFactorService::class);
    }

    public function test_login_without_2fa_goes_to_panel(): void
    {
        $response = $this->post(route('frontend.hekim.giris.post'), [
            'e_posta' => '2fa-hekim@test.com',
            'sifre' => 'sifre123',
        ]);

        $response->assertRedirect(route('hekim.panel'));
        $this->assertTrue(Auth::guard('doktor')->check());
    }

    public function test_login_with_2fa_redirects_to_challenge(): void
    {
        $secret = $this->service->generateSecret();
        $this->doktor->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['ABCD-EFGH'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->post(route('frontend.hekim.giris.post'), [
            'e_posta' => '2fa-hekim@test.com',
            'sifre' => 'sifre123',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertFalse(Auth::guard('doktor')->check());
        $this->assertEquals($this->doktor->id, session('two_factor.pending_id'));
    }

    public function test_challenge_with_valid_totp_logs_in(): void
    {
        $secret = $this->service->generateSecret();
        $this->doktor->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['ABCD-EFGH'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $code = (new Google2FA)->getCurrentOtp($secret);

        $response = $this->withSession([
            'two_factor.pending_id' => $this->doktor->id,
            'two_factor.guard' => 'doktor',
            'two_factor.remember' => false,
        ])->post(route('two-factor.challenge.post'), [
            'code' => $code,
        ]);

        $response->assertRedirect(route('hekim.panel'));
        $this->assertTrue(Auth::guard('doktor')->check());
    }

    public function test_challenge_with_recovery_code_logs_in(): void
    {
        $secret = $this->service->generateSecret();
        $this->doktor->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['WXYZ-1234'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->withSession([
            'two_factor.pending_id' => $this->doktor->id,
            'two_factor.guard' => 'doktor',
            'two_factor.remember' => false,
        ])->post(route('two-factor.challenge.post'), [
            'code' => 'WXYZ-1234',
        ]);

        $response->assertRedirect(route('hekim.panel'));
        $this->assertTrue(Auth::guard('doktor')->check());
        $this->assertNotContains('WXYZ-1234', $this->doktor->fresh()->twoFactorRecoveryCodes());
    }

    public function test_setup_confirm_enables_2fa(): void
    {
        $this->actingAs($this->doktor, 'doktor');

        $setup = $this->service->beginSetup($this->doktor);
        $code = (new Google2FA)->getCurrentOtp($setup['secret']);

        $response = $this->withSession([
            'two_factor.setup_secret' => $setup['secret'],
        ])->post(route('hekim.two-factor.confirm'), [
            'code' => $code,
        ]);

        $response->assertRedirect(route('hekim.two-factor'));
        $this->assertTrue($this->doktor->fresh()->hasTwoFactorEnabled());
    }

    public function test_verify_service_rejects_bad_code(): void
    {
        $this->assertFalse($this->service->verify($this->service->generateSecret(), '000000'));
    }
}
