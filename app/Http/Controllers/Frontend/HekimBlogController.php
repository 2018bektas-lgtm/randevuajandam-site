<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Doktor;
use App\Services\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HekimBlogController extends Controller
{
    /**
     * Display a listing of the doctor's blog posts.
     */
    public function index(): View
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $bloglar = $doktor->bloglar()->latest()->paginate(10);

        return view('hekim.blog.index', compact('bloglar'));
    }

    /**
     * Show the form for creating a new blog post.
     */
    public function create(): View
    {
        return view('hekim.blog.ekle');
    }

    /**
     * Store a newly created blog post in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string'],
            'resim' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:255'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
        ], [
            'baslik.required' => 'Başlık alanı zorunludur.',
            'icerik.required' => 'İçerik alanı zorunludur.',
            'resim.image' => 'Yüklenen dosya bir resim olmalıdır.',
            'resim.max' => 'Resim boyutu en fazla 10 MB olabilir.',
        ]);

        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();

        $data = [
            'baslik' => $request->baslik,
            'icerik' => HtmlSanitizer::clean($request->icerik),
            'meta_baslik' => $request->meta_baslik,
            'meta_aciklama' => $request->meta_aciklama,
            'meta_anahtar_kelimeler' => $request->meta_anahtar_kelimeler,
            'aktif_mi' => $request->has('aktif_mi'),
        ];

        if ($request->hasFile('resim')) {
            $data['resim'] = $request->file('resim')->store('uploads/blog', 'public');
        }

        $doktor->bloglar()->create($data);

        return redirect()->route('hekim.bloglar.index')->with('basarili', 'Blog yazınız başarıyla eklendi.');
    }

    /**
     * Show the form for editing the specified blog post.
     */
    public function edit(string $id): View
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $blog = $doktor->bloglar()->findOrFail($id);

        return view('hekim.blog.duzenle', compact('blog'));
    }

    /**
     * Update the specified blog post in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'icerik' => ['required', 'string'],
            'resim' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:255'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
        ], [
            'baslik.required' => 'Başlık alanı zorunludur.',
            'icerik.required' => 'İçerik alanı zorunludur.',
            'resim.image' => 'Yüklenen dosya bir resim olmalıdır.',
            'resim.max' => 'Resim boyutu en fazla 10 MB olabilir.',
        ]);

        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $blog = $doktor->bloglar()->findOrFail($id);

        $data = [
            'baslik' => $request->baslik,
            'icerik' => HtmlSanitizer::clean($request->icerik),
            'meta_baslik' => $request->meta_baslik,
            'meta_aciklama' => $request->meta_aciklama,
            'meta_anahtar_kelimeler' => $request->meta_anahtar_kelimeler,
            'aktif_mi' => $request->has('aktif_mi'),
        ];

        if ($request->hasFile('resim')) {
            if ($blog->resim) {
                Storage::disk('public')->delete($blog->resim);
            }

            $data['resim'] = $request->file('resim')->store('uploads/blog', 'public');
        }

        $blog->update($data);

        return redirect()->route('hekim.bloglar.index')->with('basarili', 'Blog yazınız başarıyla güncellendi.');
    }

    /**
     * Remove the specified blog post from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        /** @var Doktor $doktor */
        $doktor = Auth::guard('doktor')->user();
        $blog = $doktor->bloglar()->findOrFail($id);

        if ($blog->resim) {
            Storage::disk('public')->delete($blog->resim);
        }

        $blog->delete();

        return redirect()->route('hekim.bloglar.index')->with('basarili', 'Blog yazınız başarıyla silindi.');
    }
}
