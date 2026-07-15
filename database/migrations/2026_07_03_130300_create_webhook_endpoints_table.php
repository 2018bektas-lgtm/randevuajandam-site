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
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->nullable()->constrained('doktorlar')->cascadeOnDelete();
            $table->foreignId('klinik_id')->nullable()->constrained('klinikler')->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('secret_key', 64);
            $table->json('events')->nullable(); // subscribed events e.g. ["blog.created", "appointment.approved"]
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');
    }
};
