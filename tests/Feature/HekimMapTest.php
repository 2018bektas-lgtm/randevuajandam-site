<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Unvan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HekimMapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test doctor can save coordinates in profile.
     */
    public function test_doctor_can_save_coordinates_in_profile(): void
    {
        $il = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Kadikoy']);
        $unvan = Unvan::create(['ad' => 'Uzm. Dr.']);

        $doktor = Doktor::create([
            'ad_soyad' => 'Konum Test Hekim',
            'e_posta' => 'konum_hekim@test.com',
            'sifre' => Hash::make('sifre123'),
            'telefon' => '0 (555) 123 45 67',
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);

        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.profil.post'), [
                'ad_soyad' => 'Konum Test Hekim Güncellendi',
                'telefon' => '0 (555) 123 45 67',
                'unvan' => 'Uzm. Dr.',
                'il' => 'Istanbul',
                'ilce' => 'Kadikoy',
                'adres' => 'Yeni Klinik Adresi',
                'enlem' => '41.008212',
                'boylam' => '28.978434',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('doktorlar', [
            'id' => $doktor->id,
            'enlem' => 41.008212,
            'boylam' => 28.978434,
            'adres' => 'Yeni Klinik Adresi',
        ]);
    }

    /**
     * Test nearby distance-based search filter.
     */
    public function test_nearby_distance_based_search_filter(): void
    {
        $il1 = Il::create(['ad' => 'Istanbul', 'plaka' => '34']);
        $ilce1 = Ilce::create(['il_id' => $il1->id, 'ad' => 'Besiktas']);

        $il2 = Il::create(['ad' => 'Izmir', 'plaka' => '35']);
        $ilce2 = Ilce::create(['il_id' => $il2->id, 'ad' => 'Konak']);

        // Doctor 1 near Beşiktaş, Istanbul
        $doktorBesiktas = Doktor::create([
            'ad_soyad' => 'Merve Yakın',
            'e_posta' => 'merve_yakin@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il1->id,
            'ilce_id' => $ilce1->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'enlem' => 41.0428,
            'boylam' => 29.0075,
        ]);

        // Doctor 2 in Konak, Izmir (far away)
        $doktorIzmir = Doktor::create([
            'ad_soyad' => 'Elif Uzak',
            'e_posta' => 'elif_uzak@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il2->id,
            'ilce_id' => $ilce2->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'enlem' => 38.4189,
            'boylam' => 27.1287,
        ]);

        // Search near Beşiktaş (41.0428, 29.0075) with 20km radius
        $response = $this->get(route('frontend.hekimler', [
            'yakindaki' => '1',
            'cap' => '20',
            'user_lat' => '41.0428',
            'user_lng' => '29.0075',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Merve Yakın');
        $response->assertDontSee('Elif Uzak');
    }
}
