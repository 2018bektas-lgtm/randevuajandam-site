<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            foreach (['meeting_provider', 'meeting_room_id', 'meeting_join_token'] as $col) {
                if (Schema::hasColumn('randevular', $col)) {
                    // Unique index on meeting_join_token — drop it first if present
                    if ($col === 'meeting_join_token') {
                        try {
                            $table->dropUnique(['meeting_join_token']);
                        } catch (\Throwable) {
                            // index yoksa yut
                        }
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        // Kalici olarak kaldirildi (WebRTC sinyallesme silindi).
    }
};
