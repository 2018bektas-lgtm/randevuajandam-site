<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hasta_dosyalar')) {
            // 1) Fiziksel dosyalari sil
            foreach (DB::table('hasta_dosyalar')->pluck('dosya_yolu') as $yol) {
                if (! is_string($yol) || $yol === '') {
                    continue;
                }
                $abs = public_path(ltrim($yol, '/'));
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }

            // 2) Bos kalan dizini temizle
            $dir = public_path('uploads/hasta-dosya');
            if (is_dir($dir)) {
                @rmdir($dir);
            }

            Schema::dropIfExists('hasta_dosyalar');
        }
    }

    public function down(): void
    {
        // Kalici olarak kaldirildi.
    }
};
