<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('giderler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->enum('kategori', ['kira', 'personel', 'malzeme', 'ekipman', 'vergi', 'sigorta', 'diger'])->default('diger');
            $table->string('baslik');
            $table->decimal('tutar', 10, 2);
            $table->date('tarih');
            $table->text('aciklama')->nullable();
            $table->string('belge_yolu')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['doktor_id', 'kategori']);
            $table->index(['doktor_id', 'tarih']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giderler');
    }
};
