# Randevu Ajandam — Proje belgesi (tek kaynak)

**Son güncelleme:** 2026-07-21  
**Kapsam:** `randevuajandam-site`

---

## Ürün kararları (güncel)

| Karar | Durum |
|--------|--------|
| **Kartlı ödeme** | **Yalnızca PayTR** (iyzico kapalı) |
| **Hasta mobil uygulaması** | **Şimdilik yok** (sonra) |
| **Hekim mobil** | Var (Expo + mobile API) |
| **Çoklu şube** | Yok |
| **Fiyatlar** | KDV dahil |
| **Yorum onayı** | Sadece admin |

---

## Mimari

| Parça | Klasör |
|-------|--------|
| Ana site + mobile API | `randevuajandam-site` |
| Hekim mobil | `randevuajandam-doktor-mobile` |
| Hekim / klinik web host | `randevuajandam-hekim`, `randevuajandam-klinik` |

Canlı: `apps/randevuajandam-site` → `randevuajandam.com`.

---

## Ödeme (PayTR only)

### Web abonelik

1. Hekim / klinik paket seçer  
2. `paket_ode` veya klinik kayıt/geçiş → **PayTR iFrame + 3D Secure**  
3. Notify: `/odeme/paytr/notify`  
4. Üyelik aktif (`UyelikOdeme` + doktor/klinik tarihleri)  
5. Alternatif: **havale** (admin onayı)

### iyzico

- `IYZICO_ENABLED=false` (varsayılan)  
- Webhook → **410 disabled**  
- `IyzicoSubscriptionService::isConfigured()` false  
- Eski `iyzico_*` DB kolonları tarihçe; yeni abonelik yok  
- Admin paket formunda iyzico plan alanları gizli  

### Mobil

- Mağaza **IAP / RevenueCat** (hekim app)  
- Web’den **PayTR** de mümkün  
- IAP: `REVENUECAT_SECRET_KEY` zorunlu doğrulama; production’da client_tx yok  
- Webhook secret zorunlu  

### Env

```env
PAYMENT_DRIVER=paytr
PAYTR_MERCHANT_ID=
PAYTR_MERCHANT_KEY=
PAYTR_MERCHANT_SALT=
PAYTR_TEST_MODE=false   # prod
IYZICO_ENABLED=false
```

### PayTR iş modeli (kısa)

> B2B SaaS: hekim/klinik platform aboneliği. PayTR’den geçen bedel **muayene ücreti değildir**. Kart sitede saklanmaz.

---

## Onboarding hekim

1. Paket → kayıt (`kayit_paket_id`)  
2. Meslek / e-Devlet  
3. Domain (web paketi) → **PayTR**  
4. `?degistir=1` ile paket değişimi  

### Klinik kayıt / bireysel→klinik

- Formda **kart alanı yok**  
- Kayıt sonrası **PayTR** yönlendirme  
- Callback: klinik oluştur + üyelik  

---

## Paket yetkileri

### Hekim paneli (`paket.yetki`)

`hakkimda`, `galeri`, `randevu_talepleri`, `finans`, `blog`, `faq`, `egitimler`, `online_gorusme`, `web_sitesi`, `klinik_web_sitesi`

### Klinik bayrakları

`hasta_havuzu`, `toplu_randevu`, `merkezi_finans`, `raporlama`, `klinik_web_sitesi`  
Limit: `max_doktor_sayisi`, `max_personel_sayisi`

### Klinik hekim

```
aktifPaket() = klinik.paket
```

Tüm klinik kademelerinde hekim paneli feature’ları klinik pakete bağlı (kilit sorunu çözüldü).

| Klinik paket | Limit | Klinik paneli |
|--------------|-------|---------------|
| Başlangıç | 3 / 1 | Havuz |
| Plus | 6 / 2 | + finans, muhasebeci, toplu randevu |
| Profesyonel | 10 / 5 | + rapor |
| Özel Web | ∞ | + klinik web + domain |

Seeder: `PaketSeeder`, `KlinikSeeder` → `deploy -Seed`.

---

## Site vs mobil

| | Web | Hekim mobil | Hasta mobil |
|--|-----|-------------|-------------|
| Randevu/panel | ✅ | ✅ | — (app yok) |
| Paket ödeme | PayTR / havale | IAP | — |
| Admin | ✅ | ❌ | — |

Hasta deneyimi = **web**. Hasta API kısmi; ürün kararı: app sonra.

---

## Yapılan güvenlik / ürün düzeltmeleri (2026-07-21)

- [x] PayTR-only: klinik kayıt/geçiş, iyzico webhook kapalı  
- [x] Mobil IAP: secret yoksa client_tx kabul yok  
- [x] RevenueCat webhook secret zorunlu  
- [x] Mobil üyelik süresi middleware (`DoktorMobileToken`)  
- [x] `packageFeatures`: empty ≠ unrestricted (`restrict: true`, allowlist)  
- [x] Personel geçici şifre: tek sefer flash  
- [x] Health: PayTR test_mode / merchant kontrolü  

### Sonra (P1/P2)

- [ ] PayTR notify feature test  
- [ ] IAP transaction kalıcı tablo  
- [ ] Admin RBAC + 2FA zorunlu  
- [ ] e-Fatura  
- [ ] Hasta mobil (ertelendi)  

---

## Operasyon checklist

### PayTR prod

- [ ] Merchant ID/key/salt  
- [ ] Notify `https://DOMAIN/odeme/paytr/notify`  
- [ ] `PAYTR_TEST_MODE=false`  
- [ ] Test başarı/fail  
- [ ] `paytr_callback_logs`  

### Queue / mail

```bash
QUEUE_CONNECTION=database
php artisan queue:work --sleep=3 --tries=3
* * * * * php artisan schedule:run
```

### e-Devlet

```env
EDEVLET_AUTO_VERIFY=true
```

Admin: `/yonetim/edevlet-loglari`, meslek kuyruğu.

### Deploy

```powershell
cd deploy
powershell -ExecutionPolicy Bypass -File .\deploy.ps1 -Target site -Migrate
# paket: -Seed
```

`php artisan ra:health --strict` (prod kuralları).

---

## e-Devlet (özet)

Kayıt → YÖK barkod/PDF → otomasyon + PDF parse → auto onay veya admin kuyruğu.  
Resmi API yok; `EDEVLET_AUTO_VERIFY=false` ile kapatılabilir.  
Seeder: `MeslekProgramEslemeSeeder`.

---

## Yasal / KVKK (özet)

Canlı: `/kvkk`, `/gizlilik-politikasi`, `/kullanim-kosullari`

Eksik işletme: veri sorumlusu unvan/adres, VERBİS, hekim DPA, sağlık açık rızası, tedarikçi sözleşmeleri.  
Hukuki danışmanlık değildir.

---

## Değişiklik geçmişi

| Tarih | Not |
|-------|-----|
| 2026-07-21 | Tek `PROJE.md`; PayTR-only; IAP/üyelik enforce; hasta mobil ertelendi |
| 2026-07-21 | Paket etiket/fiyat/Klinik Plus/klinik hekim feature |
