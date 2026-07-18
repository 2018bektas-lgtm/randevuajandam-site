# Site vs mobil — özellik karşılaştırması

**Son güncelleme:** 18 Temmuz 2026  
**Amaç:** Web’de olup mobilde olmayan / eksik kalan özellikleri netleştirmek.

---

## 1. Ürün parçaları

| Parça | Klasör / konum | Kim için? |
|-------|----------------|-----------|
| Ana site (public + paneller) | `randevuajandam-site` | Hasta, hekim paneli, klinik paneli, yönetim |
| Hekim mobil (Expo) | `randevuajandam-doktor-mobile` | Hekim + personel |
| Hekim web (ayrı deploy) | `randevuajandam-hekim` | Hekim paneli host |
| Klinik web (ayrı deploy) | `randevuajandam-klinik` | Klinik paneli host |
| API iskeleti | `randevuajandam-api` | Ayrı API projesi |
| Docs site | `randevuajandam-docs` | Dokümantasyon |

**Önemli:** Repoda **hasta mobil uygulaması yok**. Hasta için backend API (`MobilePatientController` + `routes/mobile.php`) kısmen var; UI app yok.

Canlı ana site path (Hostinger):

```
domains/randevuajandam.com/public_html  →  symlink
apps/randevuajandam-site/public
```

---

## 2. Hasta tarafı

| Özellik | Web site | Hasta mobil app | Hasta API |
|---------|----------|-----------------|-----------|
| Hekim / klinik arama, filtre | ✅ | ❌ app yok | ✅ kısmi |
| Harita | ✅ | ❌ | ✅ pins |
| Hekim detay, hizmet, galeri, blog, SSS | ✅ | ❌ | ✅ kısmi |
| Misafir randevu (girişsiz) | ✅ | ❌ | ❌ |
| SMS OTP (misafir + kayıt) | ✅ | ❌ | ❌ |
| Girişli randevu | ✅ | ❌ | ✅ book |
| Randevu iptal (üye) | ✅ | ❌ | ✅ |
| Token ile randevu yönet / iCal / hesap bağla | ✅ | ❌ | ❌ |
| Eğitim listesi / detay / başvuru | ✅ | ❌ | ❌ |
| Bekleme listesine katılma | ✅ | ❌ | ❌ / zayıf |
| Yorum yazma (tamamlanan randevu) | ✅ | ❌ | ❌ |
| Online görüşme (WebRTC) | ✅ | ❌ | ❌ |
| KVKK / gizlilik / kullanım | ✅ | ❌ | — |
| Blog / paketler / pazarlama | ✅ | ❌ | — |

**Sonuç:** Hasta deneyimi = **neredeyse tamamen web**.

---

## 3. Hekim tarafı (web panel vs hekim mobil)

Hekim mobil (`ScreenId`) özet: overview, calendar, requests, waitlist, patients, services, workingHours, settings, leaves, blogs, reviews, gallery, finance*, faq, education, educationApps, profile, password, about, website, twoFactor, clinic, notifications, packages, menu.

| Özellik | Web hekim paneli | Hekim mobil |
|---------|------------------|-------------|
| Randevu / takvim / talepler | ✅ | ✅ |
| Hasta CRUD | ✅ | ✅ |
| Hizmetler | ✅ | ✅ |
| Çalışma saatleri / izin / hızlı kapat | ✅ | ✅ |
| Bekleme listesi | ✅ | ✅ |
| Blog / galeri / SSS | ✅ | ✅ |
| Eğitim + başvurular | ✅ | ✅ (UX web kadar detaylı olmayabilir) |
| Finans / bakiye / PDF | ✅ güçlü | ✅ var, web daha rahat |
| Klinik yönetim | ✅ | ✅ (API + ClinicScreen) |
| Paket / IAP | Web + Iyzico | ✅ mağaza IAP |
| 2FA | ✅ | ✅ |
| Kişisel web sitesi (DNS, API key…) | ✅ daha tam | ✅ kısmi |
| **Yorum moderasyonu** | ❌ kapalı (sadece admin) | Ekran var; API 403 — **UI yanıltıcı, işlev kapalı** |
| Offline kuyruk / push | — | ✅ güçlü |
| Yönetim paneli (admin) | ✅ sadece web | ❌ |

---

## 4. Bilinçli ürün kararları (web’de kapatıldı)

1. **Yorum onayı:** Sadece platform yöneticisi. Hekim web menüsünden kaldırıldı; mobil API `reviews*` → 403.  
2. **Public isim maskeleme:** Yorumlarda `A*** O***`.  
3. **Misafir randevu e-posta zorunlu + SMS OTP** (production’da OTP genelde açık).

---

## 5. Öncelikli “sitede var, mobilde yok” listesi

1. Hasta mobil uygulaması (arama + randevu + iptal)  
2. Misafir randevu + SMS OTP (hasta app olursa)  
3. Eğitim başvurusu (hasta)  
4. Yorum yazma (hasta)  
5. Bekleme listesine katılma (hasta)  
6. Randevu yönetim token linki  
7. Online görüşme (hasta app)  
8. Hekim mobilde yorum ekranını gizlemek (API kapalı)

---

## 6. API rotaları (referans)

- Hekim/personel/hasta mobile API: `randevuajandam-site/routes/mobile.php`  
- Hasta controller: `app/Http/Controllers/Api/MobilePatientController.php`  
- Hekim portal: `MobileDoctorPortalController.php`, `MobileDoctorController.php`, `MobileDoctorClinicController.php`  
- Personel: `MobileStaffController.php`
