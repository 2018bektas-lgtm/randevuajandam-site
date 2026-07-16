<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doktor_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();
            $table->string('token', 512)->unique();
            $table->string('platform', 20)->nullable(); // android, ios, web
            $table->string('provider', 20)->default('expo'); // expo, fcm
            $table->string('device_name', 120)->nullable();
            $table->string('app_version', 40)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['doktor_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doktor_device_tokens');
    }
};
