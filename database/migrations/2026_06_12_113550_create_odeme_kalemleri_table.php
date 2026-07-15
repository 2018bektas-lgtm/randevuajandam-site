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
        Schema::create('odeme_kalemleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odeme_id')->constrained('odemeler')->cascadeOnDelete();
            $table->decimal('tutar', 10, 2);
            $table->date('tarih');
            $table->enum('odeme_yontemi', ['nakit', 'kredi_karti', 'havale', 'online'])->default('nakit');
            $table->string('not')->nullable();
            $table->timestamps();

            $table->index(['odeme_id', 'tarih']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odeme_kalemleri');
    }
};
