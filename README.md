# Randevu Ajandam - Hekim ve Klinik Randevu SaaS Platformu

Randevu Ajandam; doktorlar, diyetisyenler, psikologlar ve kliniklerin kendi randevu ve profil yönetimlerini yapabilecekleri, ziyaretçilerin ise uzman arayıp randevu oluşturabileceği modern ve dinamik bir SaaS (Hizmet Olarak Yazılım) platformudur.

Proje Adresi: [https://github.com/bektasozcetin/randevuajandam.git](https://github.com/bektasozcetin/randevuajandam.git)

---

## 🚀 Temel Özellikler

### 1. Yönetici Paneli (`/yonetim`)
Sistem yöneticilerinin tüm platformu denetleyebileceği premium ve duyarlı (responsive) yönetim arayüzü:
*   **Doktor Yönetimi**: Kayıtlı tüm doktorların listelenmesi, hesap durumlarının (Aktif/Pasif) iOS stili anahtarla güncellenmesi, üyelik bilgileri, branş, paket ataması ve sürelerinin düzenlenmesi.
*   **Paket Yönetimi (SaaS)**: Bireysel hekim ve klinikler için aylık/yıllık üyelik paketlerinin (fiyat, indirimli fiyat ve dinamik özellik listesi ile) yönetimi.
*   **Yönetici Yönetimi**: Panel içi diğer yöneticilerin eklenmesi, düzenlenmesi ve yetkilendirilmesi.
*   **SEO Ayarları**: Platformun arama motorlarında üst sıralarda çıkması için sayfa bazlı dinamik Title, Meta Description ve Keywords ayarları.

### 2. Hekim Paneli (`/hekim/panel`)
Doktor ve kliniklerin üyelik satın aldıktan sonra eriştiği özel yönetim alanı:
*   **Profil Bilgileri**: Profil resmi yükleme, ünvan, ad-soyad ve iletişim numaralarını güncelleme.
*   **Özgeçmiş & Klinik (Hakkımda)**: Uzmanlık alanı/branş seçimi, mezun olunan üniversite, detaylı biyografi yazısı ve klinik adı (klinik üyeleri için) yönetimi.
*   **Güvenlik**: Kolay ve güvenli şifre değiştirme arayüzü.

### 3. Ziyaretçi Arayüzü (Frontend)
Kullanıcıların en uygun hekime ulaşması için geliştirilmiş modern landing page ve dizin:
*   **Gelişmiş Arama**: Uzman adı, branş veya kategoriye göre canlı arama.
*   **Hekim & Klinik Dizinleri**: Bireysel hekimlerin ve kliniklerin detaylı listesi, filtreleme seçenekleri (Ünvan, Uzmanlık).
*   **Hekim Detay Sayfası**: Hekimin özgeçmişi, biyografisi, paket türü ve çalışma bilgileri.
*   **Kayıt ve Giriş**: Hekimler için uygun SaaS paketini seçerek sisteme dahil olabilecekleri online kayıt ve onboarding akışı.

---

## 🛠️ Kullanılan Teknolojiler

*   **Framework**: Laravel 12 (PHP 8.2+)
*   **Frontend & Tasarım**: Tailwind CSS, Vanilla CSS (Outfit & Inter Google Fonts)
*   **Varlık Derleyici**: Vite JS
*   **Veritabanı**: MySQL / MariaDB
*   **Paket Yöneticileri**: Composer, NPM

> Bu klasör monorepo içinde `site/` uygulamasıdır. API ve web siteleri için kök `README.md` ve `API_MIMARI.md` dosyalarına bakın.

---

## 📦 Kurulum ve Çalıştırma

Projeyi yerel bilgisayarınızda çalıştırmak için aşağıdaki adımları takip edebilirsiniz:

1.  **Depoyu Klonlayın:**
    ```bash
    git clone https://github.com/bektasozcetin/randevuajandam.git
    cd randevuajandam/site
    ```

2.  **Bağımlılıkları Yükleyin:**
    ```bash
    composer install
    npm install
    ```

3.  **Çevre Değişkenlerini Ayarlayın:**
    `.env.example` dosyasını `.env` olarak kopyalayın ve veritabanı bilgilerinizi girin:
    ```bash
    cp .env.example .env
    ```

4.  **Uygulama Anahtarını Üretin:**
    ```bash
    php artisan key:generate
    ```

5.  **Veritabanı Tablolarını Oluşturun ve Test Verilerini Ekleyin (Seeding):**
    ```bash
    php artisan migrate --seed
    ```

6.  **Projeyi Çalıştırın:**
    ```bash
    # Laravel sunucusunu başlatın
    php artisan serve
    
    # CSS/JS derleyicisini çalıştırın
    npm run dev
    ```

---

## 🌿 Git & Branch Politikası

Projeye yapılacak tüm katkılar, geliştirme standartlarına uygun olarak ilgili dalda (branch) açılmalı, test edilmeli ve ardından `main` dalına Pull Request (PR) aracılığıyla gönderilmelidir.
