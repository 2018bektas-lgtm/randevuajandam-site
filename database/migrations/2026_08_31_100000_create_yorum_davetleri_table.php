<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Girişsiz yorum daveti.
 *
 * Önceki akışta yorum bırakmak için hasta hesabına giriş gerekiyordu.
 * Misafir randevu alan hastaya arka planda rastgele şifreli bir hesap
 * açıldığı için pratikte kimse giriş yapamıyordu; yorum tablosu boştu.
 *
 * Artık randevu saatinin 2 saat sonrasında kısa ve tek kullanımlık bir
 * bağlantı e-postalanır; hasta tıklayıp giriş yapmadan yorumunu bırakır.
 *
 * token_hash: bağlantıdaki ham token DB'de saklanmaz (veritabanı sızarsa
 * bağlantılar kullanılamasın). Arama, ham token'ın sha256'sı ile yapılır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yorum_davetleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('randevu_id')->constrained('randevular')->cascadeOnDelete();
            $table->foreignId('hasta_id')->constrained('hastalar')->cascadeOnDelete();
            $table->foreignId('doktor_id')->constrained('doktorlar')->cascadeOnDelete();

            $table->string('token_hash', 64);
            $table->timestamp('gonderildi_at')->nullable();
            $table->timestamp('kullanildi_at')->nullable();
            $table->timestamp('gecerlilik_bitis')->nullable();
            $table->timestamps();

            // Bir randevuya tek davet
            $table->unique('randevu_id');
            $table->unique('token_hash');
            $table->index(['doktor_id', 'kullanildi_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yorum_davetleri');
    }
};
