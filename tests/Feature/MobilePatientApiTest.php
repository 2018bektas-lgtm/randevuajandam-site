<?php

namespace Tests\Feature;

use App\Models\Hasta;
use App\Models\HastaApiToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilePatientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctors_list_is_public_json(): void
    {
        $res = $this->getJson('/api/mobile/v1/doctors');
        $res->assertOk()->assertJsonPath('success', true);
        $this->assertIsArray($res->json('data.items'));
    }

    public function test_register_login_me_logout_flow(): void
    {
        $email = 'mobile_test_'.uniqid().'@example.test';

        $reg = $this->postJson('/api/mobile/v1/auth/register', [
            'ad' => 'Test',
            'soyad' => 'Hasta',
            'e_posta' => $email,
            'telefon' => '05551112233',
            'sifre' => 'secret12',
            'device' => 'phpunit',
        ]);

        $reg->assertCreated()->assertJsonPath('success', true);
        $token = $reg->json('data.token');
        $this->assertNotEmpty($token);

        $me = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/v1/auth/me');
        $me->assertOk()->assertJsonPath('data.e_posta', $email);

        $logout = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/v1/auth/logout');
        $logout->assertOk();

        $me2 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/v1/auth/me');
        $me2->assertUnauthorized();
    }

    public function test_login_rejects_bad_password(): void
    {
        $hasta = Hasta::factory()->create([
            'sifre' => 'correct-pass',
            'aktif_mi' => true,
        ]);

        $res = $this->postJson('/api/mobile/v1/auth/login', [
            'e_posta' => $hasta->e_posta,
            'sifre' => 'wrong-pass',
        ]);

        $res->assertStatus(422)->assertJsonPath('success', false);
    }
}
