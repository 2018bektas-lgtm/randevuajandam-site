<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            if (! Schema::hasColumn('randevular', 'gorusme_tipi')) {
                $table->string('gorusme_tipi', 20)->default('yuz_yuze')->after('durum');
            }
            if (! Schema::hasColumn('randevular', 'meeting_provider')) {
                $table->string('meeting_provider', 30)->nullable()->after('gorusme_tipi');
            }
            if (! Schema::hasColumn('randevular', 'meeting_room_id')) {
                $table->string('meeting_room_id', 120)->nullable()->after('meeting_provider');
            }
            if (! Schema::hasColumn('randevular', 'meeting_url')) {
                $table->string('meeting_url', 500)->nullable()->after('meeting_room_id');
            }
            if (! Schema::hasColumn('randevular', 'meeting_join_token')) {
                $table->string('meeting_join_token', 64)->nullable()->unique()->after('meeting_url');
            }
            if (! Schema::hasColumn('randevular', 'meeting_baslangic_at')) {
                $table->timestamp('meeting_baslangic_at')->nullable()->after('meeting_join_token');
            }
            if (! Schema::hasColumn('randevular', 'meeting_bitis_at')) {
                $table->timestamp('meeting_bitis_at')->nullable()->after('meeting_baslangic_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('randevular', function (Blueprint $table) {
            foreach ([
                'gorusme_tipi',
                'meeting_provider',
                'meeting_room_id',
                'meeting_url',
                'meeting_join_token',
                'meeting_baslangic_at',
                'meeting_bitis_at',
            ] as $col) {
                if (Schema::hasColumn('randevular', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
