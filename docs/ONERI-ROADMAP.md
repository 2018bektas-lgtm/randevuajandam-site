# RandevuAjandam — Geliştirme ve Canlıya Çıkış Önerileri

Bu belge, mevcut ürün durumuna göre önceliklendirilmiş önerileri listeler.

**Son güncelleme:** 2026-07-20

---

## Fiyatlandırma kuralı (zorunlu)

> **Tüm paket fiyatlarına KDV dahildir.**

Gösterildiği yerler:

- Genel paketler sayfası (`/paketler`) — başlık + kart altı
- Hekim paket seçimi (`/hekim/paket-sec`)
- Ödeme özeti (`/hekim/paket-ode`)
- Mesafeli satış sözleşmesi
- Partial: `resources/views/frontend/partials/kdv-dahil.blade.php`

Gösterilen tutar = ödenecek nihai bedel (KDV ayrıca eklenmez).

---

## Mevcut durum (özet)

| Alan | Durum |
|------|--------|
| Hekim kayıt → meslek bekleme → admin onay → paket seçimi | Çalışıyor |
| PayTR iframe ödeme | Entegre |
| Domain dahil paketler | İş kuralı tanımlı |
| Misafir randevu | Var |
| Meslek onayı sonrası yönlendirme | Poll (20 sn) + e-posta + sayfa yenileme |
| Fiyat KDV ibaresi | **Zorunlu, UI + sözleşme** |
| e-Fatura (GİB API) | Yok — `fatura_durumu` flag (manuel) |
| PayTR callback log | `paytr_callback_logs` tablosu |

### Hekim onboarding akışı (tek paket seçimi)

1. **Paket seç** (`/paketler`) — KDV dahil fiyat
2. **Kayıt** (`/hekim/kayit-ol?paket=&periyot=`) — paket niyeti `kayit_paket_id` olarak saklanır
3. **Meslek onayı** bekleme → admin Onayla/Reddet → e-posta
4. Onay sonrası **doğrudan domain/ödeme** (aynı paket; tekrar seçim yok)
5. İsteğe bağlı paket değişikliği: `/hekim/paket-sec?degistir=1`

---

## Uygulanan maddeler (2026-07-20)

| # | Madde | Durum |
|---|--------|--------|
| 1 | Meslek onay / red e-postası | ✅ `MeslekBelgesiSonucBildirimi` |
| 2 | Bekleme ekranı poll | ✅ `/hekim/meslek-belgesi/durum` |
| 3 | Belge private erişim | ✅ `storage/app/private/...` + auth stream |
| 4 | PayTR notify log + fatura flag | ✅ migration + callback log |
| 5 | KVKK / sözleşme checkbox | ✅ kayıt + ödeme |
| 6 | Admin meslek filtresi + red notu | ✅ |
| 7 | Ödeme sonuç ekranı | ✅ `frontend/odeme/sonuc` |
| 8 | **Fiyatlara KDV dahildir** | ✅ tüm paket UI + sözleşme |

---

## Kalan / operasyon

### Canlıya çıkmadan (ekip)

- [ ] PayTR prod anahtarları + notify URL
- [ ] Migration: `2026_07_20_180000_add_paytr_logs_and_fatura_durumu`
- [ ] SMTP / queue worker (e-posta kuyruğu)
- [ ] Test: kayıt → onay mail → poll redirect → ödeme
- [ ] reCAPTCHA prod domain
- [ ] SSL + yedek

### Sonraki sprint (opsiyonel)

- Panel onboarding checklist
- Klinik akışını hekimle tam hizalama
- Staging ortamı + monitoring
- GİB e-Fatura API
- Admin fatura_durumu = kesildi arayüzü

---

## Deploy notu

```bash
php artisan migrate --force
# queue worker çalışıyorsa bildirimler kuyruktan gider
php artisan queue:work
```

Yeni meslek belgeleri public URL ile erişilemez; admin:
`/yonetim/doktorlar/{id}/meslek-belge`  
Hekim: `/hekim/meslek-belgesi/dosya`

Eski public `uploads/meslek-belgeleri/*` dosyaları stream endpoint üzerinden hâlâ okunur.

---

## İlgili belgeler

- `docs/paytr-is-modeli-mail.md`
- `docs/YASAL-YAPILMASI-GEREKENLER.md`
- `docs/SITE-VS-MOBIL.md`
