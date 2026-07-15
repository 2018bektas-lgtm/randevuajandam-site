<?php

namespace Tests\Feature;

use App\Models\Brans;
use App\Models\Doktor;
use App\Models\Faq;
use App\Models\Il;
use App\Models\Ilce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HekimFaqTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test doctor can manage FAQs.
     */
    public function test_doctor_can_manage_faqs(): void
    {
        $il = Il::create(['ad' => 'Bursa', 'plaka' => '16']);
        $ilce = Ilce::create(['il_id' => $il->id, 'ad' => 'Nilufer']);
        $brans = Brans::create(['ad' => 'Fizyoterapi']);

        $doktor = Doktor::create([
            'ad_soyad' => 'Hasan Hekim',
            'e_posta' => 'hasan@test.com',
            'sifre' => Hash::make('sifre123'),
            'il_id' => $il->id,
            'ilce_id' => $ilce->id,
            'tur' => 'bireysel',
            'aktif_mi' => true,
            'uzmanlik_alani' => 'Fizyoterapi',
        ]);
        $doktor->branslar()->attach($brans->id);

        // 1. View empty FAQ list
        $response = $this->actingAs($doktor, 'doktor')
            ->get(route('hekim.faqs.index'));

        $response->assertStatus(200);
        $response->assertSee('Henüz Soru Eklemediniz');

        // 2. Create FAQ
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.faqs.store'), [
                'soru' => 'İlk Soru Nedir?',
                'cevap' => 'Bu ilk sorunun cevabıdır.',
                'sira' => 1,
            ]);

        $response->assertRedirect(route('hekim.faqs.index'));
        $this->assertDatabaseHas('faqs', [
            'doktor_id' => $doktor->id,
            'soru' => 'İlk Soru Nedir?',
            'cevap' => 'Bu ilk sorunun cevabıdır.',
            'sira' => 1,
            'aktif' => true,
        ]);

        $faq = Faq::where('soru', 'İlk Soru Nedir?')->first();

        // 3. View FAQ in public details page
        $response = $this->get($doktor->profil_url);
        $response->assertStatus(200);
        $response->assertSee('İlk Soru Nedir?');
        $response->assertSee('Bu ilk sorunun cevabıdır.');
        $response->assertSee('FAQPage');

        // 4. Update FAQ
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.faqs.update', $faq->id), [
                'soru' => 'Güncellenmiş Soru?',
                'cevap' => 'Güncellenmiş cevap içeriği.',
                'sira' => 2,
            ]);

        $response->assertRedirect(route('hekim.faqs.index'));
        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'soru' => 'Güncellenmiş Soru?',
            'cevap' => 'Güncellenmiş cevap içeriği.',
            'sira' => 2,
        ]);

        // 5. Toggle FAQ active status
        $response = $this->actingAs($doktor, 'doktor')
            ->post(route('hekim.faqs.toggle', $faq->id));

        $response->assertRedirect(route('hekim.faqs.index'));
        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'aktif' => false,
        ]);

        // Verify it is NOT visible on public profile since it is inactive now
        $response = $this->get($doktor->profil_url);
        $response->assertStatus(200);
        $response->assertDontSee('Güncellenmiş Soru?');

        // 6. Delete FAQ
        $response = $this->actingAs($doktor, 'doktor')
            ->delete(route('hekim.faqs.destroy', $faq->id));

        $response->assertRedirect(route('hekim.faqs.index'));
        $this->assertDatabaseMissing('faqs', [
            'id' => $faq->id,
        ]);
    }

    /**
     * Test doctor cannot manage another doctor's FAQs.
     */
    public function test_doctor_cannot_manage_other_doctors_faqs(): void
    {
        $doktor1 = Doktor::create([
            'ad_soyad' => 'Hasan Hekim 1',
            'e_posta' => 'hasan1@test.com',
            'sifre' => Hash::make('sifre123'),
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);

        $doktor2 = Doktor::create([
            'ad_soyad' => 'Hasan Hekim 2',
            'e_posta' => 'hasan2@test.com',
            'sifre' => Hash::make('sifre123'),
            'tur' => 'bireysel',
            'aktif_mi' => true,
        ]);

        $faqOfDoktor2 = Faq::create([
            'doktor_id' => $doktor2->id,
            'soru' => 'Doktor 2 Sorusu',
            'cevap' => 'Doktor 2 Cevabı',
            'aktif' => true,
            'sira' => 1,
        ]);

        // Doktor 1 tries to update Doktor 2's FAQ
        $response = $this->actingAs($doktor1, 'doktor')
            ->post(route('hekim.faqs.update', $faqOfDoktor2->id), [
                'soru' => 'Hacklendi',
                'cevap' => 'Hacklendi',
            ]);

        $response->assertStatus(404);

        // Doktor 1 tries to toggle Doktor 2's FAQ status
        $response = $this->actingAs($doktor1, 'doktor')
            ->post(route('hekim.faqs.toggle', $faqOfDoktor2->id));

        $response->assertStatus(404);

        // Doktor 1 tries to delete Doktor 2's FAQ
        $response = $this->actingAs($doktor1, 'doktor')
            ->delete(route('hekim.faqs.destroy', $faqOfDoktor2->id));

        $response->assertStatus(404);
    }
}
