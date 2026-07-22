# Finans / Muhasebe Yapılandırma Planı  
## Hekim · Klinik · Mobil

**Randevu Ajandam** · 2026-07-22  
**Durum:** Plan — onay sonrası uygulama  
**Kapsam:** Site (hekim + klinik), mobil hekim/personel, mevcut model/migration üzerinden eksiklerin kapatılması  

---

## 1. Mevcut durum (özet)

İki paralel katman var:

| Katman | Tablolar | Kime ait |
|--------|----------|----------|
| **Hekim cari / P&L** | `odemeler`, `odeme_kalemleri`, `giderler`, `finans_kategoriler` | Solo hekim + kliniğe bağlı hekim paneli |
| **Klinik merkezi** | `klinik_giderleri`, `klinik_hakedisler` + hekim `odemeler` rollup | Klinik sahibi / finans yetkisi |

**Hasta bakiyesi ayrı tablo değil** — türetilir:  
`SUM(tutar − odenen_tutar)` açık faturalarda (`beklemede` / `kismi_odeme`).

### Ne tamam?

| | Web hekim | Mobil hekim | Web klinik | Mobil klinik | Personel |
|--|-----------|-------------|------------|--------------|----------|
| Gelir CRUD + kalem | ✅ | ✅ | ❌ (sadece toplam) | ❌ | Sadece tam ödeme |
| Gider CRUD | ✅ | ⚠️ alan uyumsuz | Ekle/sil | ✅ | ❌ |
| Kategori | ✅ | ⚠️ gelire bağlı değil | Serbest string | Serbest string | ❌ |
| Hasta bakiyesi / hesap | ✅ | ✅ (kısmi) | ❌ | ❌ | ❌ |
| Hakediş | ❌ | ❌ | ✅ | ✅ | ❌ |
| PDF rapor | ✅ | ⚠️ özet | ✅ | ✅ | ❌ |

### Ana tutarsızlıklar

1. **Gider kategorisi ikiliği:** `giderler.kategori` (enum) + `finans_kategori_id`; mobil string `kategori` ister, web `baslik` + kategori id.  
2. **Hakediş ≠ genel bakış:** hakediş `created_at` + `tutar` kullanıyor; finans özeti `odeme_tarihi` + `odenen_tutar` + iptal hariç.  
3. **Personel / hasta kartı tahsilat:** çoğu zaman **yeni tam ödenmiş fatura** açıyor; açık faturaya kalem yazmıyor.  
4. **Klinik gelir listesi yok** — sadece doktor gelirlerinin toplamı.  
5. **Mobil gelir formunda** hasta / hizmet / kategori seçimi eksik; özet kartında `son_odemeler` API’den geliyor ama UI göstermiyor.

---

## 2. Hedef ürün modeli

### 2.1 Tek dil (domain)

```
Fatura (Odeme)     = borç belgesi (tutar)
  └─ Kalemler      = tahsilat satırları (odenen_tutar toplamı)
Gider              = hekim kişisel veya klinik genel gider
Kategori           = gelir | gider (hekim veya klinik sahibi)
Hasta hesabı       = o hastanın fatura + tahsilat zaman çizelgesi
Hakediş            = dönemsel tahsilattan klinik komisyonu
```

**Kural (her yerde aynı):**  
Tahsilat = açık faturaya **kalem**; mümkünse yeni “tam ödenmiş hayalet fatura” açma.

### 2.2 Kapsam eksenleri

| Eksen | Hekim paneli | Klinik paneli |
|-------|--------------|---------------|
| **Hasta bazlı** | Kendi faturaları / bakiyesi | Tüm hekimlerin hasta AR’si |
| **Kategori bazlı** | `finans_kategoriler` (doktor) | Klinik kategori seti (aşağıda) |
| **Gelir / gider** | `odemeler` + `giderler` | Rollup gelir + `klinik_giderleri` |
| **Rapor** | Aylık P&L, AR, yöntem, PDF | Klinik P&L, hakediş, hekim katkısı |

---

## 3. Veri modeli — migration yeterli mi?

### 3.1 Yeterli olanlar (yeni tablo şart değil)

- `odemeler` + `odeme_kalemleri` — fatura / taksit  
- `giderler` + `finans_kategoriler` — hekim  
- `klinik_giderleri` + `klinik_hakedisler` — klinik  
- Randevu tamamlanınca otomatik fatura (`RandevuFinansKaydet`)

### 3.2 Önerilen küçük şema tamamlamaları (opsiyonel ama temiz)

| Değişiklik | Neden |
|------------|--------|
| `finans_kategoriler.klinik_id` nullable | Klinik ortak kategori (hekim `doktor_id` null, klinik dolu) |
| Veya ayrı `klinik_finans_kategoriler` | Daha izole; iki UI sözleşmesi |
| `giderler.baslik` zorunlu her API’de | Web/mobil uyumu |
| Legacy `giderler.kategori` enum → seed + `finans_kategori_id` | Tek kaynak |
| Hakediş hesap motoru (kod) | Migration değil; sorgu düzeltmesi |

**Karar önerisi (Faz 1):** Yeni tablo **zorunlu değil**. Önce servis + UI + hakediş sorgu düzeltmesi. Klinik kategori: serbest string kalsın veya `finans_kategoriler`’a `klinik_id` eklensin (Faz 2).

---

## 4. Hedef ekran matrisi

### 4.1 Hekim — web + mobil (aynı API sözleşmesi)

| Ekran | İçerik |
|-------|--------|
| **Özet** | Bu ay tahsilat / gider / net / açık alacak; son 5 gelir + son 5 gider; mini grafik veya 6 ay metin |
| **Gelirler** | Filtre: tarih, durum, kategori, hasta, yöntem · CRUD · kalem ekle/sil |
| **Giderler** | Filtre: tarih, kategori · baslik zorunlu · belge · CRUD |
| **Kategoriler** | Gelir / gider sekmeleri · CRUD · kullanımda silinemez |
| **Hasta bakiyeleri** | Sadece borçlular + arama · detaya git |
| **Hasta hesabı** | Faturalar, kalan, tahsilat (açık faturaya), borç ekle |
| **Rapor** | Tarih aralığı · P&L · kategori · yöntem · PDF/CSV |

### 4.2 Hasta kartı (web + mobil)

| Bölüm | Davranış |
|-------|----------|
| Cari özet | Tahsil edilen / kalan borç |
| Hareket listesi | Faturalar + kalemler (zaman sırası) |
| **Tahsilat** | Açık fatura seç → tutar/yöntem/tarih → `collect` API |
| **Borç ekle** | Yeni fatura (+ opsiyonel ilk ödeme) |
| Randevu bitince | Otomatik fatura (zaten var) → kartta görünsün |

### 4.3 Klinik — web + mobil

| Ekran | İçerik |
|-------|--------|
| **Genel bakış** | Toplam tahsilat (hekimler), klinik gider, net, bekleyen AR, hekim payı |
| **Gelir (salt okunur liste)** | Hekim bazlı / hasta bazlı drill-down (yeni) |
| **Giderler** | CRUD + baslik + kategori + belge + (opsiyonel) tekrarlı |
| **Hakediş** | Dönem, oran, **tahsilat bazlı** hesap, durum |
| **Hasta AR (klinik)** | Tüm hekim açık bakiyeleri (yeni) |
| **Rapor** | P&L + hakediş özeti + PDF |

### 4.4 Personel

| Ekran | Davranış |
|-------|----------|
| Günlük kasa | Bugünkü tahsilatlar |
| Tahsilat | **Açık fatura / hasta seç** → collect (yeni fatura değil) |
| İptal | Yetki ile |

---

## 5. API sözleşmesi (mobil + web ortak)

Tek servis katmanı önerisi: `App\Services\Finance\FinanceService`  
(Web controller + MobileDoctorPortalController + Personel + Clinic rollup buradan okur.)

### Hekim

```
GET    /finance/overview?ay=
GET    /finance/incomes?baslangic&bitis&durum&kategori_id&hasta_id&yontem&q
POST   /finance/incomes          { hasta_id?, hizmet_id?, finans_kategori_id?, tutar, odeme_tarihi,
                                   ilk_odeme_tutar?, ilk_odeme_yontemi, aciklama? }
... kalemler, PUT/DELETE
GET    /finance/expenses?baslangic&bitis&kategori_id
POST   /finance/expenses         { baslik, tutar, tarih, finans_kategori_id?, aciklama?, belge? }
GET/POST/PUT/DELETE /finance/categories
GET    /finance/balances?q&sadece_borclu=1
GET    /finance/patients/{id}
POST   /finance/patients/{id}/collect   { odeme_id, tutar, tarih, odeme_yontemi, not? }
POST   /finance/patients/{id}/debt      { tutar, odeme_tarihi, aciklama?, ilk_odeme_...? }
GET    /finance/report?baslangic&bitis&format=json|pdf
```

### Klinik

```
GET    /clinic/finance/overview
GET    /clinic/finance/incomes?baslangic&bitis&doktor_id&hasta_id   ← yeni (read-only rollup)
GET    /clinic/finance/balances                                      ← yeni
GET/POST/PUT/DELETE /clinic/expenses
GET/POST /clinic/settlements  (+ status)
GET    /clinic/reports[.pdf]
```

### Personel

```
GET  /staff/payments?tarih
POST /staff/payments/collect   { odeme_id, tutar, ... }   ← tercih
POST /staff/payments           (legacy full-pay — kademeli kaldır)
```

---

## 6. Uygulama fazları

### Faz A — Temel tutarlılık (1–2 gün) ⭐ önce

1. **`FinanceService` iskeleti** — incomes list/create, collect, balances (mevcut SQL’i taşı).  
2. **Hakediş sorgu düzeltmesi:**  
   - `odeme_tarihi` (veya kalem `tarih`)  
   - `odenen_tutar`  
   - `durum != iptal` + soft delete  
3. **Mobil gider DTO:** `baslik` zorunlu; kategori = `finans_kategori_id` + isim yedek.  
4. **Mobil gelir formu:** hasta (opsiyonel), kategori, yöntem — mevcut API alanlarını kullan.  
5. **Hasta kartı:** tahsilat → `collect` (açık fatura varsa); yoksa debt + full pay.  
6. **Özet ekranı:** `son_odemeler` / `son_giderler` listesi.

### Faz B — Hasta & kategori derinliği

1. Gelir/gider filtreleri: durum, kategori, yöntem, hasta (API + UI web/mobil).  
2. Hasta hesabı: zaman çizelgesi, iptal/void (soft: `durum=iptal`).  
3. Kategori silmede “kullanımda” kontrolü (mobil = web).  
4. Personel: açık fatura tahsilatı.  
5. Randevu faturası hasta kartında “bekleyen” olarak işaretli.

### Faz C — Klinik tamamlama

1. Klinik gelir listesi (rollup, salt okunur).  
2. Klinik hasta bakiyeleri.  
3. Klinik gider: baslik + belge + tekrarlı UI (`tekrarli_mi`).  
4. Klinik kategori (serbest string → kontrollü liste veya `klinik_id`’li kategori).  
5. Rapor: kategori P&L + yöntem + hekim katkısı.

### Faz D — Rapor & cilâ

1. Ortak PDF şablonu (web + mobil base64).  
2. CSV export.  
3. AR aging (0–30 / 30–60 / 60+).  
4. Günlük kasa (yöntem bazlı).  
5. Grafikler (opsiyonel).

---

## 7. UI iskeleti (mobil hekim — hedef)

```
Finans
├── Özet (KPI + son hareketler + kısayollar)
├── Gelirler [filtre çubuğu]
├── Giderler [filtre]
├── Kategoriler
├── Hasta bakiyeleri → Hasta hesabı
└── Rapor [tarih aralığı + PDF]

Hasta detayı
└── Cari
    ├── Özet kart
    ├── Hareketler
    ├── Tahsilat (açık fatura)
    └── Borç ekle
```

### Klinik (sahip)

```
Klinik → Finans | Gider | Hakediş | Rapor | (Hasta AR)
```

---

## 8. Paket / yetki

| Özellik | Kapı |
|---------|------|
| Hekim finans paneli | `paket.yetki:finans` |
| Klinik merkezi finans | `klinik.paket:merkezi_finans` + `finans_yonetimi` |
| Hakediş | `hakedis_yonetimi` + `merkezi_finans` |
| Klinik rapor | `raporlama` |
| Personel ödeme | personel `yetkiler.odeme` |

Klinik hekim: kişisel finans hâlâ **kendi** `odemeler` / `giderler` üzerinden (klinik paket feature’ları ile açık).

---

## 9. Karar tablosu (onay)

| Madde | Öneri |
|-------|--------|
| Yeni büyük migration | **Hayır** (Faz A–B); isteğe bağlı `klinik_id` kategori Faz C |
| Tek servis katmanı | **Evet** `FinanceService` |
| Tahsilat modeli | **Kalem on open fatura** (personel + hasta kartı) |
| Hakediş baz | **Tahsil edilen** (`odenen_tutar`) + `odeme_tarihi` |
| Gider standardı | `baslik` + `finans_kategori_id` (legacy enum seed’e) |
| Klinik gelir | Rollup list (yeni fatura entity yok) |
| Mobil öncelik | Özet + gelir filtre + hasta tahsilat + gider kategori |

---

## 10. İlk sprint (somut iş listesi)

Onay sonrası sıra:

1. [ ] `FinanceService` + hakediş fix  
2. [ ] Mobil `FinanceScreen` son hareketler  
3. [ ] Mobil gelir: hasta + kategori select  
4. [ ] Mobil gider: kategori picker + baslik  
5. [ ] Hasta kartı: collect vs new income ayrımı  
6. [ ] Web hekim filtreleri ile mobil filtre parity (tarih+durum+kategori)  
7. [ ] Klinik overview sayılar = hakediş ile aynı motor  

---

## 11. Tek cümle

> Model yeterli; asıl iş **tek muhasebe dili + tutarlı tahsilat/hakediş + web-mobil-klinik UI parity**.  
> Önce servis ve kurallar, sonra ekran derinliği.

---

*Dosya: `docs/plan-finans-muhasebe.md`*  
*Onay sonrası Faz A implementasyonuna geçilir.*
