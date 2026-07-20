# e-Devlet otomatik mezuniyet doğrulama

## Akış

1. Paket seç  
2. Kayıt adım 1: hesap + TC  
3. Adım 2: YÖK barkod / PDF → `POST /hekim/kayit-ol/mezuniyet-dogrula`  
4. e-Devlet form otomasyonu + PDF parse + ad/TC eşleşme + program mapping  
5. Adım 3: unvan/branş (otomatik dolu) + KVKK → kayıt  
6. `auto_onay_uygun` ise `meslek_dogrulama_durumu=onaylandi` → ödeme  
   değilse `beklemede` → admin kuyruğu  

## Config

```env
EDEVLET_AUTO_VERIFY=true
EDEVLET_TIMEOUT=25
EDEVLET_AD_ESIK=0.85
```

## Bağımlılık

`smalot/pdfparser` (composer) — sunucuda `zip` extension gerekir.  
Yoksa ham PDF metin çıkarma + `pdftotext` fallback.

## Tablolar

- `meslek_program_eslemeleri` — seeder: `MeslekProgramEslemeSeeder`
- `doktor_mezuniyet_belgeleri`
- `edevlet_dogrulama_loglari`

```bash
php artisan migrate --force
php artisan db:seed --class=MeslekProgramEslemeSeeder --force
```

## Not

Resmi e-Devlet API yok; HTML değişirse servis bozulabilir. Flag ile kapatın.
