# Operasyon checklist — Randevu Ajandam

## PayTR prod

- [ ] Merchant ID / key / salt (canlı)
- [ ] Notify URL: `https://DOMAIN/odeme/paytr/notify` (CSRF kapalı)
- [ ] OK / Fail URL
- [ ] Test ödemesi başarı + fail
- [ ] Admin: PayTR callback log (`paytr_callback_logs`)
- [ ] `PAYTR_TEST_MODE=false` prod’da

## Mail / kuyruk

```bash
# .env
QUEUE_CONNECTION=database   # veya redis
MAIL_MAILER=smtp
# SMTP host/user/pass

# Supervisor / cron
php artisan queue:work --sleep=3 --tries=3
* * * * * cd /path && php artisan schedule:run
```

Schedule: randevu hatırlatma, klinik üyelik, **doktor:uyelik-hatirlat** (09:15).

## e-Devlet

```env
EDEVLET_AUTO_VERIFY=true
EDEVLET_TIMEOUT=28
EDEVLET_RETRY=2
```

- Admin: `/yonetim/edevlet-loglari`
- Meslek kuyruğu: `/yonetim/doktorlar/meslek-kuyruk`
- Fail → yüklenen PDF parse fallback

## Composer / PDF

Sunucuda `zip` extension + `composer install` ile `smalot/pdfparser`.

## Staging

Ayrı DB + PayTR test mağaza; prod’da canlı TC/barkod denemesi minimumda.

## Monitoring

- Laravel log + failed_jobs
- Disk: `storage/app/private`
- 500 → mail/Slack (opsiyonel)
- e-Devlet fail oranı (admin log)

## Fatura

Admin `/yonetim/faturalar` — `fatura_durumu` bekliyor/kesildi (manuel dönem).

## Deploy

```bash
# PC
.\deploy\deploy.ps1 -Target site -Migrate
php artisan db:seed --class=MeslekProgramEslemeSeeder --force   # sunucuda
```
