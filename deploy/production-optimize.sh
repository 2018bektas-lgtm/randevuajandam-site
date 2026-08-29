#!/usr/bin/env bash
# Hostinger production: pull + env harden + caches
# Kullanım (sunucuda proje klasöründe):
#   bash deploy/production-optimize.sh
# veya:
#   cd ~/domains/.../apps/randevuajandam-site && bash deploy/production-optimize.sh

set -euo pipefail

echo "==> Proje dizini: $(pwd)"

if [[ ! -f artisan ]]; then
  echo "HATA: artisan bulunamadı. Laravel kök dizinine gidin."
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "HATA: .env yok."
  exit 1
fi

echo "==> Git pull (varsa)"
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  git pull --ff-only origin main || git pull --ff-only || true
  git log -1 --oneline || true
else
  echo "Uyarı: git repo değil, pull atlandı."
fi

echo "==> Composer (production)"
# ONEMLI: bu adim sessizce gecilirse uygulama ESKI autoloader ile calisir.
# Gecmiste tam olarak bu yuzden app/helpers.php yuklenmedi ve hekim profil /
# hizmet detay / blog sayfalari "Call to undefined function plain_text()" ile
# 500 verdi. Bu nedenle hata artik yutulmuyor.
if ! command -v composer >/dev/null 2>&1; then
  echo "HATA: composer bulunamadi. Autoloader uretilemez, dagitim durduruldu."
  exit 1
fi
composer install --no-dev --optimize-autoloader --no-interaction
composer dump-autoload --optimize --no-interaction

echo "==> Autoloader dogrulamasi"
php -r '
require "vendor/autoload.php";
$eksik = [];
foreach (["plain_text", "decode_text"] as $f) {
    if (! function_exists($f)) { $eksik[] = "function ".$f."()"; }
}
if ($eksik) {
    fwrite(STDERR, "HATA: autoloader eksik -> ".implode(", ", $eksik)."
");
    exit(1);
}
echo "  helper fonksiyonlari yuklu
";
'

echo "==> .env production sertleştirme (mevcut değerleri günceller)"
# APP_ENV / APP_DEBUG
if grep -q '^APP_ENV=' .env; then
  sed -i.bak 's/^APP_ENV=.*/APP_ENV=production/' .env
else
  echo 'APP_ENV=production' >> .env
fi
if grep -q '^APP_DEBUG=' .env; then
  sed -i.bak 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
else
  echo 'APP_DEBUG=false' >> .env
fi
# LOG
if grep -q '^LOG_LEVEL=' .env; then
  sed -i.bak 's/^LOG_LEVEL=.*/LOG_LEVEL=error/' .env
else
  echo 'LOG_LEVEL=error' >> .env
fi
# Cache / session — shared hosting için file daha hızlı
if grep -q '^CACHE_STORE=' .env; then
  sed -i.bak 's/^CACHE_STORE=.*/CACHE_STORE=file/' .env
elif grep -q '^CACHE_DRIVER=' .env; then
  sed -i.bak 's/^CACHE_DRIVER=.*/CACHE_DRIVER=file/' .env
else
  echo 'CACHE_STORE=file' >> .env
fi
if grep -q '^SESSION_DRIVER=' .env; then
  sed -i.bak 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
else
  echo 'SESSION_DRIVER=file' >> .env
fi
# Queue
if grep -q '^QUEUE_CONNECTION=' .env; then
  sed -i.bak 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/' .env
else
  echo 'QUEUE_CONNECTION=database' >> .env
fi
# OTP (misafir randevu + kayıt)
if grep -q '^RANDEVU_OTP_REQUIRED=' .env; then
  sed -i.bak 's/^RANDEVU_OTP_REQUIRED=.*/RANDEVU_OTP_REQUIRED=true/' .env
else
  echo 'RANDEVU_OTP_REQUIRED=true' >> .env
fi

rm -f .env.bak 2>/dev/null || true

echo "==> Kuyruk yapılandırması"
# QUEUE_CONNECTION=sync iken ShouldQueue bildirimleri (SMTP/SMS/WhatsApp) HTTP
# istegi icinde calisir ve randevu formunu bloklar. Degeri burada zorla
# degistirmiyoruz: worker calismiyorsa "database" secmek bildirimleri sessizce
# durdurur. Onun yerine durumu gorunur kiliyoruz.
QUEUE_CONN=$(grep -E '^QUEUE_CONNECTION=' .env | head -n1 | cut -d= -f2- | tr -d '"'"'"'[:space:]')
if [[ "${QUEUE_CONN:-sync}" == "sync" ]]; then
  echo "  UYARI: QUEUE_CONNECTION=sync"
  echo "         Bildirimler istek icinde gonderiliyor; randevu formu yavas."
  echo "         Onerilen: .env icinde QUEUE_CONNECTION=database"
  echo "         (routes/console.php icindeki zamanlanmis queue:work kuyrugu"
  echo "          her dakika bosaltir; ek bir daemon gerekmez.)"
else
  echo "  QUEUE_CONNECTION=${QUEUE_CONN}"
  BEKLEYEN=$(php artisan tinker --execute='echo \DB::table("jobs")->count();' 2>/dev/null | tail -n1 | tr -dc '0-9')
  BASARISIZ=$(php artisan tinker --execute='echo \DB::table("failed_jobs")->count();' 2>/dev/null | tail -n1 | tr -dc '0-9')
  echo "  Bekleyen is: ${BEKLEYEN:-?} · Basarisiz is: ${BASARISIZ:-?}"
  if [[ "${BEKLEYEN:-0}" -gt 500 ]]; then
    echo "  UYARI: Kuyruk birikmis. schedule:run cron'u calisiyor mu kontrol edin."
  fi
fi

echo "==> Storage dirs (file session/cache için zorunlu)"
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache/data \
         storage/framework/testing \
         storage/logs \
         storage/app/public \
         bootstrap/cache
touch storage/framework/sessions/.gitignore \
      storage/framework/views/.gitignore \
      storage/framework/cache/data/.gitignore \
      storage/logs/.gitignore 2>/dev/null || true

echo "==> Storage link / permissions"
php artisan storage:link 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "==> Migrate"
# Migration hatasi yutulmamali: yarim migrate edilmis sema ile devam etmek
# tespit edilmesi en zor hatalari uretiyor.
php artisan migrate --force

echo "==> Optimize caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true
php artisan optimize 2>/dev/null || true

echo "==> Uploads publish (varsa)"
php artisan uploads:publish 2>/dev/null || true

echo ""
echo "==> Durum"
php artisan about 2>/dev/null | head -n 40 || true

echo ""
echo "TAMAM. Kontrol edin:"
echo "  - Site ana sayfa açılıyor mu"
echo "  - APP_DEBUG=false (php artisan about -> Debug Mode OFF)"
echo "  - Cron: * * * * * php artisan schedule:run  (queue:work bunun icinde)"
echo "  - SMS_DRIVER production'da log olmamalı (OTP için)"
