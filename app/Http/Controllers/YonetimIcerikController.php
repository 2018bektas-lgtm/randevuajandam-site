<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Faq;
use App\Models\Hizmet;
use App\Models\DoktorGaleri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class YonetimIcerikController extends Controller
{
    /**
     * List all services across all doctors.
     */
    public function hizmetler()
    {
        $yonetici = Auth::guard('yonetici')->user();
        $hizmetler = Hizmet::with('doktor')->latest()->paginate(20);
        return view('yonetim.hizmetler.index', compact('yonetici', 'hizmetler'));
    }

    /**
     * Delete a service.
     */
    public function hizmetSil($id)
    {
        $hizmet = Hizmet::findOrFail($id);
        $hizmet->delete();
        return back()->with('basarili', 'Hizmet başarıyla silindi.');
    }

    /**
     * Toggle active status of a service.
     */
    public function hizmetDurum($id)
    {
        $hizmet = Hizmet::findOrFail($id);
        $hizmet->aktif_mi = !$hizmet->aktif_mi;
        $hizmet->save();
        return back()->with('basarili', 'Hizmet durumu başarıyla güncellendi.');
    }

    /**
     * List all blog posts across all doctors.
     */
    public function bloglar()
    {
        $yonetici = Auth::guard('yonetici')->user();
        $bloglar = Blog::with('doktor')->latest()->paginate(20);
        return view('yonetim.bloglar.index', compact('yonetici', 'bloglar'));
    }

    /**
     * Delete a blog post.
     */
    public function blogSil($id)
    {
        $blog = Blog::findOrFail($id);
        if ($blog->resim && file_exists(public_path($blog->resim))) {
            @unlink(public_path($blog->resim));
        }
        $blog->delete();
        return back()->with('basarili', 'Blog yazısı başarıyla silindi.');
    }

    /**
     * List all FAQs across all doctors.
     */
    public function faqs()
    {
        $yonetici = Auth::guard('yonetici')->user();
        $faqs = Faq::with('doktor')->latest()->paginate(20);
        return view('yonetim.faqs.index', compact('yonetici', 'faqs'));
    }

    /**
     * Delete an FAQ.
     */
    public function faqSil($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();
        return back()->with('basarili', 'Soru başarıyla silindi.');
    }

    /**
     * Toggle active status of an FAQ.
     */
    public function faqDurum($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->aktif = !$faq->aktif;
        $faq->save();
        return back()->with('basarili', 'Soru durumu başarıyla güncellendi.');
    }

    /**
     * List all gallery images across all doctors.
     */
    public function galeriler()
    {
        $yonetici = Auth::guard('yonetici')->user();
        $galeriler = DoktorGaleri::with('doktor')->latest()->paginate(20);
        return view('yonetim.galeriler.index', compact('yonetici', 'galeriler'));
    }

    /**
     * Delete a gallery image.
     */
    public function galeriSil($id)
    {
        $galeri = DoktorGaleri::findOrFail($id);
        if ($galeri->resim_yolu && file_exists(public_path($galeri->resim_yolu))) {
            @unlink(public_path($galeri->resim_yolu));
        }
        $galeri->delete();
        return back()->with('basarili', 'Galeri görseli başarıyla silindi.');
    }
}
