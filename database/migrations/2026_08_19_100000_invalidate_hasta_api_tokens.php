<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eski düz metin token'lar hash'e taşınamaz (istemcideki kopya bilinmiyor).
        // Tüm satırları geçersiz kılıyoruz; kullanıcılar yeniden giriş yapar.
        DB::table('hasta_api_tokens')->delete();
    }

    public function down(): void
    {
        // Geri dönüş yok — token'lar zaten silinmiş.
    }
};
