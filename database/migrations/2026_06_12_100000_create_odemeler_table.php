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
        Schema::create('odemeler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->foreignId('randevu_id')->nullable()->constrained('randevular')->nullOnDelete();
            $table->foreignId('hasta_id')->nullable()->constrained('hastalar')->nullOnDelete();
            $table->foreignId('hizmet_id')->nullable()->constrained('hizmetler')->nullOnDelete();
            $table->decimal('tutar', 10, 2);
            $table->decimal('odenen_tutar', 10, 2)->default(0.00);
            $table->enum('odeme_yontemi', ['nakit', 'kredi_karti', 'havale', 'online'])->default('nakit');
            $table->enum('durum', ['beklemede', 'odendi', 'kismi_odeme', 'iptal'])->default('beklemede');
            $table->text('aciklama')->nullable();
            $table->date('odeme_tarihi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['doktor_id', 'durum']);
            $table->index(['doktor_id', 'odeme_tarihi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odemeler');
    }
};
