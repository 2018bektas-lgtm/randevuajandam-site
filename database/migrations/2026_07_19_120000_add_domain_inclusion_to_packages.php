<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * En yüksek web sitesi paketlerinde domain pakete dahil (ayrı domain ücreti yok).
 * Hekim: Özel Web Sitesi · Klinik: Kurumsal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paketler', function (Blueprint $table) {
            if (! Schema::hasColumn('paketler', 'domain_dahil_mi')) {
                $table->boolean('domain_dahil_mi')->default(false)->after('aktif_mi');
            }
            if (! Schema::hasColumn('paketler', 'domain_dahil_yil')) {
                $table->unsignedTinyInteger('domain_dahil_yil')->default(1)->after('domain_dahil_mi');
            }
            if (! Schema::hasColumn('paketler', 'domain_dahil_tlds')) {
                $table->json('domain_dahil_tlds')->nullable()->after('domain_dahil_yil');
            }
        });

        if (! Schema::hasTable('domain_orders')) {
            Schema::create('domain_orders', function (Blueprint $table) {
                $table->id();
                $table->morphs('owner');
                $table->foreignId('paket_id')->nullable()->constrained('paketler')->nullOnDelete();
                $table->string('domain', 180);
                $table->string('tld', 30)->nullable();
                $table->string('kaynak', 20)->default('included'); // included | byod
                $table->string('durum', 30)->default('draft');
                $table->string('hostinger_item_id')->nullable();
                $table->string('hostinger_order_id')->nullable();
                $table->unsignedInteger('hostinger_cost_cents')->nullable();
                $table->string('currency', 8)->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('registered_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->unique(['domain']);
                $table->index(['durum']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_orders');

        Schema::table('paketler', function (Blueprint $table) {
            foreach (['domain_dahil_tlds', 'domain_dahil_yil', 'domain_dahil_mi'] as $col) {
                if (Schema::hasColumn('paketler', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
