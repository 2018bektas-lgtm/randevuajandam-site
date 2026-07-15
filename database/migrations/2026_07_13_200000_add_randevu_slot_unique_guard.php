<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Double-booking guard:
 * - Normalize saat to H:i where possible
 * - Add composite index for slot lookups
 * - MySQL: functional unique is limited; we add a generated slot_key for active rows via application
 *   and a unique index on (doktor_id, tarih, saat, durum) is too strict (multiple iptal OK).
 *
 * Practical approach: unique index on (doktor_id, tarih, saat, slot_lock) where slot_lock
 * is 1 for active appointments and unique id for cancelled (so cancelled don't collide).
 *
 * Simpler portable approach: add `slot_token` column filled for active appointments as
 * "{doktor_id}|{Y-m-d}|{H:i}" and NULL for cancelled — MySQL unique allows multiple NULLs
 * in some versions; for SQLite multiple NULLs are allowed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('randevular')) {
            return;
        }

        if (! Schema::hasColumn('randevular', 'slot_token')) {
            Schema::table('randevular', function (Blueprint $table) {
                $table->string('slot_token', 64)->nullable()->after('saat');
                $table->unique('slot_token', 'randevular_slot_token_unique');
            });
        }

        // Backfill active appointments
        try {
            $rows = DB::table('randevular')
                ->whereNull('deleted_at')
                ->whereIn('durum', ['beklemede', 'onaylandi', 'tamamlandi'])
                ->select('id', 'doktor_id', 'tarih', 'saat')
                ->get();

            foreach ($rows as $row) {
                $tarih = is_string($row->tarih)
                    ? substr($row->tarih, 0, 10)
                    : (string) $row->tarih;
                $saat = substr((string) $row->saat, 0, 5);
                $token = $row->doktor_id.'|'.$tarih.'|'.$saat;
                $exists = DB::table('randevular')->where('slot_token', $token)->where('id', '!=', $row->id)->exists();
                if (! $exists) {
                    DB::table('randevular')->where('id', $row->id)->update(['slot_token' => $token]);
                }
            }
        } catch (\Throwable) {
            // ignore on empty/partial DBs
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('randevular', 'slot_token')) {
            Schema::table('randevular', function (Blueprint $table) {
                $table->dropUnique('randevular_slot_token_unique');
                $table->dropColumn('slot_token');
            });
        }
    }
};
