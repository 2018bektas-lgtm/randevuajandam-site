<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Doktor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HekimFaqController extends Controller
{
    /**
     * Display a listing of the doctor's FAQs.
     */
    public function index()
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        
        $faqs = $doktor->faqs()->orderBy('sira')->orderBy('id')->get();

        return view('hekim.faq.index', compact('doktor', 'faqs'));
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $validated = $request->validate([
            'soru' => 'required|string|max:255',
            'cevap' => 'required|string',
            'sira' => 'nullable|integer|min:0',
        ], [
            'soru.required' => 'Soru alanı zorunludur.',
            'soru.max' => 'Soru en fazla 255 karakter olabilir.',
            'cevap.required' => 'Cevap alanı zorunludur.',
        ]);

        $doktor->faqs()->create([
            'soru' => $validated['soru'],
            'cevap' => $validated['cevap'],
            'sira' => $validated['sira'] ?? 0,
            'aktif' => true,
        ]);

        return redirect()->route('hekim.faqs.index')->with('basarili', 'Sıkça Sorulan Soru başarıyla oluşturuldu.');
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, $id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $faq = $doktor->faqs()->findOrFail($id);

        $validated = $request->validate([
            'soru' => 'required|string|max:255',
            'cevap' => 'required|string',
            'sira' => 'nullable|integer|min:0',
        ], [
            'soru.required' => 'Soru alanı zorunludur.',
            'soru.max' => 'Soru en fazla 255 karakter olabilir.',
            'cevap.required' => 'Cevap alanı zorunludur.',
        ]);

        $faq->update([
            'soru' => $validated['soru'],
            'cevap' => $validated['cevap'],
            'sira' => $validated['sira'] ?? 0,
        ]);

        return redirect()->route('hekim.faqs.index')->with('basarili', 'Sıkça Sorulan Soru başarıyla güncellendi.');
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy($id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $faq = $doktor->faqs()->findOrFail($id);
        $faq->delete();

        return redirect()->route('hekim.faqs.index')->with('basarili', 'Sıkça Sorulan Soru başarıyla silindi.');
    }

    /**
     * Toggle the active status of the specified FAQ.
     */
    public function toggleAktif($id)
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $faq = $doktor->faqs()->findOrFail($id);
        $faq->update([
            'aktif' => !$faq->aktif
        ]);

        return redirect()->route('hekim.faqs.index')->with('basarili', 'Soru durumu başarıyla güncellendi.');
    }
}
