<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hasta_api_tokens')) {
            return;
        }

        Schema::create('hasta_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasta_id')->constrained('hastalar')->cascadeOnDelete();
            $table->string('token', 80)->unique();
            $table->string('name')->nullable();
            $table->string('device')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['hasta_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasta_api_tokens');
    }
};
