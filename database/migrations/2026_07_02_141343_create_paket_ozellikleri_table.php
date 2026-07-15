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
        // 1. Create features table
        Schema::create('paket_ozellikleri', function (Blueprint $table) {
            $table->id();
            $table->string('kod')->unique(); // e.g. 'finans', 'blog', 'yorum'
            $table->string('ad');
            $table->text('aciklama')->nullable();
            $table->timestamps();
        });

        // 2. Create many-to-many pivot table
        Schema::create('paket_ozellik_pivot', function (Blueprint $table) {
            $table->foreignId('paket_id')->constrained('paketler')->onDelete('cascade');
            $table->foreignId('ozellik_id')->constrained('paket_ozellikleri')->onDelete('cascade');
            $table->primary(['paket_id', 'ozellik_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_ozellik_pivot');
        Schema::dropIfExists('paket_ozellikleri');
    }
};
