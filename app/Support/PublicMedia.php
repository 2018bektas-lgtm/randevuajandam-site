<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * public/uploads ve storage/app/public (symlink) yolları için tek yerden yükle/sil.
 */
class PublicMedia
{
    public static function delete(?string $path): void
    {
        if ($path === null || trim($path) === '') {
            return;
        }

        $path = trim(str_replace('\\', '/', $path));

        if (preg_match('#^https?://#i', $path)) {
            if (preg_match('#/(uploads/.+)$#i', $path, $m)) {
                $path = $m[1];
            } elseif (preg_match('#/storage/(uploads/.+)$#i', $path, $m)) {
                $path = $m[1];
            } else {
                return;
            }
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        // storage/uploads/x → uploads/x + storage disk
        if (str_starts_with($path, 'storage/')) {
            $rel = substr($path, strlen('storage/'));
            try {
                Storage::disk('public')->delete($rel);
            } catch (\Throwable) {
            }
            $abs = public_path('storage/'.$rel);
            if (is_file($abs)) {
                @unlink($abs);
            }
            $path = $rel;
        }

        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable) {
        }

        $publicFile = public_path($path);
        if (is_file($publicFile)) {
            @unlink($publicFile);
        }
    }

    /**
     * Dosyayı public/uploads/{subdir}/ altına kaydet (paylaşılan medya kökü).
     */
    public static function store(UploadedFile $file, string $subdir = 'uploads'): string
    {
        $subdir = trim(str_replace('\\', '/', $subdir), '/');
        $dir = public_path($subdir);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $name = 'doktor_'.time().'_'.Str::lower(Str::random(8)).'.'.$ext;
        $file->move($dir, $name);

        return $subdir.'/'.$name;
    }
}
