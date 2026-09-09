<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair stale slot_token values on cancelled or soft-deleted appointments.
 *
 * Older releases could leave slot_token filled after soft-delete because
 * Eloquent's SoftDeletes uses raw UPDATE and skipped the `saving` model hook.
 * That leftover value keeps the unique index locked and causes "randevu saati
 * maalesef doludur" errors when re-booking the same slot.
 *
 * The application model now clears slot_token on soft-delete via the `deleted`
 * event; this migration cleans up the existing rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('randevular') || ! Schema::hasColumn('randevular', 'slot_token')) {
            return;
        }

        DB::table('randevular')
            ->whereNotNull('deleted_at')
            ->whereNotNull('slot_token')
            ->update(['slot_token' => null]);

        DB::table('randevular')
            ->whereNull('deleted_at')
            ->where('durum', 'iptal')
            ->whereNotNull('slot_token')
            ->update(['slot_token' => null]);
    }

    public function down(): void
    {
        // No-op: we do not restore stale tokens.
    }
};
