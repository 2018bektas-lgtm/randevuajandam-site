<?php

/**
 * Sistem paket özellikleri kataloğu (Excel matrisi + middleware kodları).
 * Yönetim panelinde tıklanır; manuel serbest metin girilmez.
 * Vitrin metinleri /paketler sayfasında otomatik listelenir.
 *
 * @return array{katalog: list<array{kod:string,ad:string,aciklama:?string,grup:string,sira:int,vitrin:bool}>}
 */
return [
    'katalog' => [
        // ── Randevu ──
        ['kod' => 'randevu_talebi_goruntule', 'ad' => 'Randevu talebini görme', 'aciklama' => 'Bekleyen talepleri listede görür; onaylayamaz (Vitrin duvarı).', 'grup' => 'Randevu', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'randevu_talepleri', 'ad' => 'Randevu talebini onaylama / reddetme', 'aciklama' => 'Talepleri onayla, ertele, reddet.', 'grup' => 'Randevu', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'online_takvim', 'ad' => 'Online randevu takvimi', 'aciklama' => 'Takvim, slot, randevu oluşturma.', 'grup' => 'Randevu', 'sira' => 30, 'vitrin' => true],
        ['kod' => 'bekleme_listesi', 'ad' => 'Bekleme listesi', 'aciklama' => 'Dolu slotlar için bekleme listesi.', 'grup' => 'Randevu', 'sira' => 40, 'vitrin' => true],
        ['kod' => 'hizli_slot', 'ad' => 'Hızlı slot kapatma / bloklama', 'aciklama' => 'Gün/saat aralığı hızlı kapatma.', 'grup' => 'Randevu', 'sira' => 50, 'vitrin' => true],
        ['kod' => 'seri_randevu', 'ad' => 'Tekrarlayan (seri) randevu', 'aciklama' => 'Periyodik randevu tanımlama.', 'grup' => 'Randevu', 'sira' => 60, 'vitrin' => true],
        ['kod' => 'ical_export', 'ad' => 'Takvim dışa aktarma (iCal)', 'aciklama' => 'Google/Apple takvim dışa aktarma.', 'grup' => 'Randevu', 'sira' => 70, 'vitrin' => true],

        // ── Hatırlatma (WhatsApp yok — bilinçli olarak dahil edilmedi) ──
        ['kod' => 'email_bildirim', 'ad' => 'E-posta randevu bildirimi', 'aciklama' => 'Hasta/hekim e-posta bildirimleri.', 'grup' => 'Hatırlatma', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'sms_hatirlatma', 'ad' => 'SMS hatırlatma (kontörlü)', 'aciklama' => 'Aylık SMS kotası paket alanından yönetilir.', 'grup' => 'Hatırlatma', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'sms_baslik', 'ad' => 'Kendi firma adıyla SMS başlığı', 'aciklama' => 'Özel SMS gönderici başlığı.', 'grup' => 'Hatırlatma', 'sira' => 30, 'vitrin' => true],
        ['kod' => 'no_show_mesaj', 'ad' => 'No-show geri kazanım mesajı (SMS)', 'aciklama' => 'Gelinmeyen randevu sonrası otomatik SMS.', 'grup' => 'Hatırlatma', 'sira' => 40, 'vitrin' => true],

        // ── Hasta ──
        ['kod' => 'hasta_kartlari', 'ad' => 'Hasta / danışan kartları', 'aciklama' => 'Hasta listesi ve temel kart.', 'grup' => 'Hasta', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'hasta_not_dosya', 'ad' => 'Hasta notu ve dosya yükleme', 'aciklama' => 'Not, görsel, dosya.', 'grup' => 'Hasta', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'tedavi_gecmisi', 'ad' => 'Tedavi geçmişi ve seans takibi', 'aciklama' => 'Seans / tedavi geçmişi.', 'grup' => 'Hasta', 'sira' => 30, 'vitrin' => true],
        ['kod' => 'onam_formu', 'ad' => 'Onam formu / dijital form', 'aciklama' => 'Dijital onam ve formlar.', 'grup' => 'Hasta', 'sira' => 40, 'vitrin' => true],
        ['kod' => 'hasta_export', 'ad' => 'Hasta listesi Excel dışa aktarma', 'aciklama' => 'Hasta listesini Excel olarak indir.', 'grup' => 'Hasta', 'sira' => 50, 'vitrin' => true],

        // ── Profil ──
        ['kod' => 'profil_sayfasi', 'ad' => 'Hekim profil sayfası', 'aciklama' => 'Platform profili.', 'grup' => 'Profil', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'dogrulanmis_rozet', 'ad' => 'Doğrulanmış hekim rozeti', 'aciklama' => 'Belge onaylı rozet.', 'grup' => 'Profil', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'iletisim_profilde', 'ad' => 'Telefon / adres profilde görünür', 'aciklama' => 'İletişim bilgilerinin profilde açılması.', 'grup' => 'Profil', 'sira' => 30, 'vitrin' => true],
        ['kod' => 'hakkimda', 'ad' => 'Hakkımda / özgeçmiş', 'aciklama' => 'Detaylı özgeçmiş ve mezuniyet.', 'grup' => 'Profil', 'sira' => 40, 'vitrin' => true],
        ['kod' => 'galeri', 'ad' => 'Fotoğraf galerisi', 'aciklama' => 'Muayenehane / klinik görselleri.', 'grup' => 'Profil', 'sira' => 50, 'vitrin' => true],
        ['kod' => 'dis_baglanti', 'ad' => 'Dış bağlantılar (Instagram, LinkedIn)', 'aciklama' => 'Sosyal ve web linkleri.', 'grup' => 'Profil', 'sira' => 60, 'vitrin' => true],
        ['kod' => 'oncelikli_liste', 'ad' => 'Branş / şehirde öne çıkarma', 'aciklama' => 'Arama sonuçlarında öncelik.', 'grup' => 'Profil', 'sira' => 70, 'vitrin' => true],

        // ── Yorum ──
        ['kod' => 'yorum_gorunur', 'ad' => 'Danışan yorumları profilde', 'aciklama' => 'Onaylı yorumların vitrinde görünmesi.', 'grup' => 'Yorum', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'yorum_yanit', 'ad' => 'Yorumlara yanıt verme', 'aciklama' => 'Hekim yanıtı yazabilir.', 'grup' => 'Yorum', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'yorum_davet', 'ad' => 'Randevu sonrası yorum daveti', 'aciklama' => 'Otomatik yorum daveti.', 'grup' => 'Yorum', 'sira' => 30, 'vitrin' => true],

        // ── Finans ──
        ['kod' => 'finans', 'ad' => 'Gelir / gider takibi', 'aciklama' => 'Temel finans paneli.', 'grup' => 'Finans', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'hasta_bakiyeleri', 'ad' => 'Hasta bakiyeleri ve tahsilat', 'aciklama' => 'Bakiye ve tahsilat takibi.', 'grup' => 'Finans', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'finans_rapor', 'ad' => 'Aylık gelir raporu (PDF)', 'aciklama' => 'PDF finansal rapor.', 'grup' => 'Finans', 'sira' => 30, 'vitrin' => true],

        // ── İçerik ──
        ['kod' => 'blog', 'ad' => 'Blog ve makale yayınlama', 'aciklama' => 'Blog paneli.', 'grup' => 'İçerik', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'faq', 'ad' => 'S.S.S. yönetimi', 'aciklama' => 'Sık sorulan sorular.', 'grup' => 'İçerik', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'egitimler', 'ad' => 'Eğitim / kurs vitrini', 'aciklama' => 'Eğitim ve başvuru formu.', 'grup' => 'İçerik', 'sira' => 30, 'vitrin' => true],

        // ── Online / Web ──
        ['kod' => 'online_gorusme', 'ad' => 'Görüntülü online görüşme', 'aciklama' => 'Platform video odası.', 'grup' => 'Online & Web', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'web_sitesi', 'ad' => 'Kişisel hekim web sitesi', 'aciklama' => 'CMS + tema; domain paket alanından.', 'grup' => 'Online & Web', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'klinik_web_sitesi', 'ad' => 'Klinik web sitesi', 'aciklama' => 'Çok hekimli klinik sitesi.', 'grup' => 'Online & Web', 'sira' => 30, 'vitrin' => true],

        // ── Destek ──
        ['kod' => 'destek_email', 'ad' => 'E-posta destek', 'aciklama' => 'E-posta destek hattı.', 'grup' => 'Destek', 'sira' => 10, 'vitrin' => true],
        ['kod' => 'destek_oncelikli', 'ad' => 'Öncelikli destek + kurulum', 'aciklama' => 'Birebir kurulum ve öncelik.', 'grup' => 'Destek', 'sira' => 20, 'vitrin' => true],
        ['kod' => 'veri_tasima', 'ad' => 'Veri taşıma desteği', 'aciklama' => 'Mevcut sistemden aktarım.', 'grup' => 'Destek', 'sira' => 30, 'vitrin' => true],
    ],
];
