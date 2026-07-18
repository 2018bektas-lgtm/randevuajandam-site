# Yasal yapılması gerekenler — Randevu Ajandam

Bu belge, platformun **KVKK (6698)**, gizlilik, kullanım koşulları, randevu/sağlık verisi ve aracı model risklerine göre **işletmenin tamamlaması gereken** adımları listeler.

> **Not:** Hukuki danışmanlık değildir. Nihai metin ve süreçler avukat / KVKK danışmanı onayıyla kesinleşmelidir.  
> **Son güncelleme:** 18 Temmuz 2026  
> **İlgili canlı sayfalar:**  
> - `/kvkk` — KVKK Aydınlatma Metni  
> - `/gizlilik-politikasi` — Gizlilik Politikası  
> - `/kullanim-kosullari` — Kullanım Koşulları  

---

## Mevcut durum (kısa)

| Konu | Durum |
|------|--------|
| KVKK / Gizlilik / Kullanım sayfaları | Canlıda var (taslak + platforma özel içerik) |
| Formlarda KVKK onay kutusu | Randevu, eğitim, bekleme listesi vb. |
| SMS OTP (misafir randevu + kayıt) | Kod tarafında var |
| Yorum: hekim seçici onaylayamaz | Platform admin moderasyonu |
| Veri sorumlusu unvan / adres | **Eksik — doldurulmalı** |
| VERBİS | **Kontrol edilmeli** |
| Hekim–platform KVKK protokolü | **Yazılmalı** |
| Sağlık verisi ayrı açık rıza | **Netleştirilmeli** |
| Tedarikçi veri işleyen sözleşmeleri | **Dosyalanmalı** |

---

## 1. Hemen (bu hafta)

### 1.1 Veri sorumlusu / şirket kimliği
Yasal sayfalar, fatura ve destek imzasında **aynı** bilgiler:

- [ ] Ticari unvan (şirket / şahıs şirketi)
- [ ] Açık adres
- [ ] Vergi dairesi ve vergi numarası
- [ ] MERSİS no (varsa)
- [ ] KVKK iletişim e-postası (`info@…` veya `kvkk@…`)
- [ ] Yetkili imza sahibi (iç kayıt)

**Neden:** KVKK m.10 aydınlatmada “veri sorumlusunun kimliği” zorunlu unsur.

**Nereye işlenecek:** KVKK, Gizlilik, Kullanım sayfaları + fatura + (ileride) Site Ayarları alanları.

---

### 1.2 VERBİS
- [ ] Mali müşavir / avukat ile VERBİS yükümlülüğü kontrolü
- [ ] Zorunluysa kayıt + veri kategorisi envanteri
- [ ] Muafsa: muafiyet gerekçesi dosyada not

---

### 1.3 Avukat / KVKK danışmanı onayı
- [ ] Mevcut 3 yasal metni 1 tur okutup onaylatmak
- [ ] Özellikle: sağlık verisi, aracı platform rolü, yurt dışı aktarım, yorum moderasyonu
- [ ] Onaylı sürüm + tarih arşivi (PDF)

---

### 1.4 Hekim / klinik KVKK ek protokolü
Paket onayı veya sözleşme ekinde yazılı:

- [ ] Kim veri sorumlusu, kim veri işleyen (platform vs hekim/klinik)
- [ ] Hasta verisinin aktarımı ve amaçları
- [ ] Silme / düzeltme talebi süreci
- [ ] Veri ihlali bildirimi (kimin ne yapacağı, süre)
- [ ] Yorumların platform moderasyonunda olduğu
- [ ] Hekim kendi klinik notundan sorumlu olduğu

---

## 2. Kısa vade (1–4 hafta)

### 2.1 Sağlık / randevu notu — açık rıza
Randevu notunda şikayet vb. yazılabiliyorsa özel nitelikli (sağlık) veri riski vardır.

- [ ] Not alanını net etiketlemek (“sağlık bilgisi içerebilir”)
- [ ] Ayrı **açık rıza** metni + onay kutusu (genel KVKK tikinden ayrı)
- [ ] Rıza kaydı: kim, ne zaman, hangi metin sürümü

---

### 2.2 Tedarikçi (veri işleyen) sözleşmeleri
Dosyada DPA / standart taahhüt veya sağlayıcı sözleşmesi:

| Tedarikçi | Amaç | Durum |
|-----------|------|--------|
| Hostinger | Barındırma | [ ] |
| SMS (Netgsm / İleti Merkezi vb.) | OTP, bildirim | [ ] |
| Iyzico | Ödeme | [ ] |
| E-posta (SMTP) | Bildirim maili | [ ] |
| Expo / FCM / APNs (kullanılıyorsa) | Push | [ ] |
| Google / Meta analitik (açıksa) | İzleme | [ ] |

- [ ] Production’da `SMS_DRIVER=log` olmamalı (OTP gerçek SMS)
- [ ] Iyzico production anahtarları ve webhook secret dolu olmalı

---

### 2.3 Çerez ve analitik
- [ ] GTM / GA4 / Meta Pixel **kullanılıyor mu** karar ver
- [ ] Kullanılıyorsa: çerez bilgilendirme + tercihen rıza bandı
- [ ] Kullanılmıyorsa: kapalı bırak (risk düşer)

---

### 2.4 İç prosedürler (kısa yazılı not yeterli)
- [ ] **KVKK başvurusu:** kanal (e-posta), 30 gün cevap, kim yanıtlar
- [ ] **Veri ihlali:** tespit → kayıt → (gerekirse) Kurul / ilgili kişi
- [ ] **Hesap / veri silme talebi:** ne silinir, ne yasal süreyle tutulur
- [ ] Tek destek kanalı: `info@…` veya `kvkk@…`

---

### 2.5 Hekim doğrulama (güven + yasal risk)
- [ ] Diploma / unvan beyanı veya doğrulama süreci
- [ ] Sahte profil şüphesinde askıya alma politikası
- [ ] İletişim (telefon/e-posta) doğrulama

---

## 3. Operasyon (sürekli)

| İş | Sıklık | Durum |
|----|--------|--------|
| Yasal metin sürümü + “Son güncelleme” tarihi | Her değişiklik | [ ] |
| Onay kaydı (metin versiyonu + tarih + mümkünse IP) | Her KVKK/rıza onayı | [ ] |
| Çalışan / destek erişim yetkisi gözden geçirme | 3–6 ay | [ ] |
| Yedek + erişim logu kontrolü | Düzenli | [ ] |
| Production env: `APP_DEBUG=false`, gerçek SMS/ödeme | Canlıda her zaman | [ ] |

---

## 4. Ürün / kod tarafı (sonra halledeceğiz)

Teknik olarak yapılabilecekler (iş kararı + avukat metni ile):

- [ ] Site Ayarları’na yasal kimlik alanları (unvan, adres, vergi no…) → yasal sayfalara otomatik basma
- [ ] Randevu formuna sağlık verisi **ayrı açık rıza** kutusu
- [ ] KVKK başvuru formu sayfası (`/kvkk-basvuru` vb.)
- [ ] Onay kayıtları tablosu (versiyon + kullanıcı/misafir + IP + tarih)
- [ ] Çerez paneli (analitik/reklam açıksa)
- [ ] Hasta panelinden hesap silme / veri silme talebi akışı
- [ ] Hekim paneline KVKK ek protokol kabul kaydı

---

## 5. Risk özeti (hatırlatma)

| Seviye | Ne zaman |
|--------|----------|
| Yüksek risk | Hiç aydınlatma / onay yok, sağlık verisi kontrolsüz |
| **Orta risk (mevcut)** | Taslak metin + onay + OTP var; unvan/VERBİS/protokol eksik |
| Düşük–orta | Unvan + avukat + hekim protokolü + sağlık rızası |
| İyi uyum | + VERBİS + DPA + çerez + ihlal planı + silme süreci |

---

## 6. Önerilen uygulama sırası

1. Şirket unvanı + adres + iletişim → yasal sayfalara işle  
2. VERBİS kontrolü  
3. Avukat 1 tur onay  
4. Hekim/klinik KVKK ek protokolü  
5. Sağlık notu açık rızası + tedarikçi DPA’ları  
6. Çerez/analitik kararı  
7. Kod: ayarlar alanları, rıza, başvuru formu, onay logu  

---

## 7. İlgili dosyalar (kod)

| Dosya | Açıklama |
|-------|----------|
| `resources/views/frontend/legal/kvkk.blade.php` | KVKK aydınlatma |
| `resources/views/frontend/legal/gizlilik.blade.php` | Gizlilik politikası |
| `resources/views/frontend/legal/kullanim.blade.php` | Kullanım koşulları |
| `resources/views/frontend/legal/_layout.blade.php` | Ortak yasal sayfa iskeleti |
| `app/Http/Controllers/Frontend/LegalController.php` | Rotalar / son güncelleme tarihi |
| Footer yasal linkleri | `resources/views/frontend/layouts/partials/footer.blade.php` |

**Canlı URL’ler:**

- https://randevuajandam.com/kvkk  
- https://randevuajandam.com/gizlilik-politikasi  
- https://randevuajandam.com/kullanim-kosullari  

---

## 8. Notlar

- Platform **tıbbi teşhis/tedavi taahhüt etmez**; hekim–danışan ilişkisinin mesleki sorumluluğu hekim/kliniktedir (kullanım koşullarında belirtilmeli ve hekim sözleşmesinde pekiştirilmeli).  
- Yorumlar: adil moderasyon (hekim seçici onaylamaz) bilinçli bir ürün kararıdır; hakaret/ifşa içerikleri admin onayında elenmeli.  
- Bu checklist tamamlandıkça bu dosyadaki kutular işaretlenebilir.
