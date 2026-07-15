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
        Schema::create('yorumlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasta_id')->constrained('hastalar')->cascadeOnDelete();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->foreignId('randevu_id')->constrained('randevular')->cascadeOnDelete();
            $table->unsignedTinyInteger('puan');
            $table->text('yorum');
            $table->text('doktor_yaniti')->nullable();
            $table->enum('onay_durumu', ['beklemede', 'onaylandi', 'reddedildi'])->default('beklemede');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hasta_id', 'randevu_id']);
            $table->index(['doktor_id', 'onay_durumu']);
            $table->index('puan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yorumlar');
    }
};
