<?php

/**
 * Bildirim metin şablonları (e-posta konu / SMS).
 * Yer tutucular: {hasta}, {doktor}, {tarih}, {saat}, {hizmet}, {vakit}, {hekim_notu},
 * {gorusme_tipi}, {gorusme_linki}, {gorusme_notu}
 *
 * İleride yönetim panelinden DB'ye taşınabilir; şimdilik config tek kaynak.
 */
return [

    'randevu_onaylandi' => [
        'mail_subject' => 'Randevunuz Onaylandı - Randevu Ajandam',
        'mail_intro' => '{doktor} ile olan randevu talebiniz onaylanmıştır. Görüşme tipi: {gorusme_tipi}.',
        'sms' => 'Sayin {hasta}, {doktor} ile randevunuz onaylanmistir. Tarih: {tarih} Saat: {saat}. {gorusme_notu} Saglikli gunler dileriz.',
    ],

    'randevu_iptal_hasta' => [
        'mail_subject' => 'Randevunuz İptal Edildi - Randevu Ajandam',
        'mail_intro' => '{doktor} ile olan randevunuz hekim tarafından iptal edilmiştir.',
        'sms' => 'Sayin {hasta}, {doktor} ile olan randevunuz iptal edilmistir. Tarih: {tarih} Saat: {saat}.',
    ],

    'randevu_iptal_doktor' => [
        'mail_subject' => 'Randevu İptal Edildi - Randevu Ajandam',
        'mail_intro' => '{hasta} isimli hastanın randevu talebi hasta tarafından iptal edilmiştir.',
        'sms' => 'Sayin {doktor}, {hasta} isimli hastanin randevusu iptal edilmistir. Tarih: {tarih} Saat: {saat}.',
    ],

    'randevu_hatirlatma' => [
        'mail_subject' => 'Randevu Hatırlatması - Randevu Ajandam',
        'mail_intro' => '{doktor} ile olan randevunuza **{vakit}** kalmıştır. Görüşme: {gorusme_tipi}.',
        'sms' => 'Sayin {hasta}, {doktor} ile randevunuza {vakit} kalmistir. Tarih: {tarih} Saat: {saat}. {gorusme_notu} Saglikli gunler dileriz.',
    ],

    'yeni_randevu_manuel' => [
        'mail_subject' => 'Yeni Randevu Talebi Var - Randevu Ajandam',
        'mail_intro' => 'Randevu onayınızı beklemektedir ({gorusme_tipi}). Lütfen hekim panelinden onaylayınız.',
        'sms' => 'Sayin {doktor}, yeni randevu talebiniz var ({gorusme_tipi}). Hasta: {hasta} Tarih: {tarih} Saat: {saat}. Onay bekliyor.',
    ],

    'yeni_randevu_otomatik' => [
        'mail_subject' => 'Yeni Randevu Oluşturuldu (Otomatik Onay) - Randevu Ajandam',
        'mail_intro' => 'Randevu otomatik olarak onaylanmıştır ({gorusme_tipi}) ve takviminize işlenmiştir.',
        'sms' => 'Sayin {doktor}, {hasta} icin yeni randevu olusturuldu ({gorusme_tipi}). Tarih: {tarih} Saat: {saat}.',
    ],

    'bekleme_listesi_slot' => [
        'mail_subject' => 'Boş randevu slotu - Randevu Ajandam',
        'mail_intro' => '{doktor} için bekleme listenizdeki bir randevu saati açılmış olabilir.',
        'sms' => 'Sayin {hasta}, {doktor} icin uygun randevu acilmis olabilir. Profil: {link}',
    ],

];
