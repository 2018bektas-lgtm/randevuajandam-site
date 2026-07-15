<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doktor_api_tokens')) {
            return;
        }

        Schema::create('doktor_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->string('token', 80)->unique();
            $table->string('name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['doktor_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doktor_api_tokens');
    }
};
