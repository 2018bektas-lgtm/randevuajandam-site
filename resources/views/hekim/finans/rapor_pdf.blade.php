<!DOCTYPE html>
<html lang="tr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Finansal Rapor</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #C96A2B;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header table {
            width: 100%;
        }
        .logo-title {
            font-size: 18px;
            font-weight: bold;
            color: #C96A2B;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            color: #444444;
        }
        .meta-text {
            color: #666666;
            font-size: 10px;
        }
        .meta-text-right {
            color: #666666;
            font-size: 10px;
            text-align: right;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #111111;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 12px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .stats-box {
            border: 1px solid #E5E7EB;
            padding: 12px;
            text-align: center;
            width: 25%;
            background-color: #FAFAFA;
        }
        .stats-label {
            font-size: 9px;
            color: #666666;
            text-transform: uppercase;
            font-weight: bold;
        }
        .stats-val {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        .text-green { color: #10B981; }
        .text-red { color: #EF4444; }
        .text-orange { color: #F59E0B; }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #FAFAFA;
            border-bottom: 1px solid #E5E7EB;
            padding: 8px;
            font-weight: bold;
            text-align: left;
            font-size: 9px;
            color: #4B5563;
        }
        .data-table td {
            border-bottom: 1px solid #F3F4F6;
            padding: 8px;
            font-size: 10px;
            color: #333333;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            font-size: 8px;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
        }
        .badge-green { background-color: #D1FAE5; color: #065F46; }
        .badge-amber { background-color: #FEF3C7; color: #92400E; }
        .badge-blue { background-color: #DBEAFE; color: #1E40AF; }
        .badge-red { background-color: #FEE2E2; color: #991B1B; }

        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 30px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 8px;
            color: #9CA3AF;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="logo-title">Randevu Ajandam</div>
                    <div class="meta-text">Hekim Finansal Durum Raporu</div>
                </td>
                <td style="text-align: right;">
                    <div class="report-title">{{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}</div>
                    <div class="meta-text-right">Rapor Dönemi: {{ $tarihBaslangic->format('d.m.Y') }} - {{ $tarihBitis->format('d.m.Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Finansal Özet</div>
    <table class="stats-table">
        <tr>
            <td class="stats-box">
                <div class="stats-label">Toplam Tahsilat</div>
                <div class="stats-val text-green">{{ number_format($toplamGelir, 2, ',', '.') }} ₺</div>
            </td>
            <td class="stats-box" style="border-left: none;">
                <div class="stats-label">Toplam Gider</div>
                <div class="stats-val text-red">{{ number_format($toplamGider, 2, ',', '.') }} ₺</div>
            </td>
            <td class="stats-box" style="border-left: none;">
                <div class="stats-label">Net Kâr</div>
                <div class="stats-val {{ $netKar >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($netKar, 2, ',', '.') }} ₺
                </div>
            </td>
            <td class="stats-box" style="border-left: none;">
                <div class="stats-label">Bekleyen Alacak</div>
                <div class="stats-val text-orange">{{ number_format($toplamTahsilEdilmeyen, 2, ',', '.') }} ₺</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Gelir Kayıtları</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Tarih</th>
                <th style="width: 25%;">Hasta</th>
                <th style="width: 25%;">Hizmet</th>
                <th style="width: 15%;">Yöntem</th>
                <th style="width: 10%;" class="text-right">Ödenen</th>
                <th style="width: 10%;" class="text-right">Durum</th>
            </tr>
        </thead>
        <tbody>
            @forelse($odemeler as $odeme)
                <tr>
                    <td>{{ $odeme->odeme_tarihi ? $odeme->odeme_tarihi->format('d.m.Y') : $odeme->created_at->format('d.m.Y') }}</td>
                    <td>
                        {{ $odeme->hasta ? $odeme->hasta->ad_soyad : ($odeme->randevu ? $odeme->randevu->ad . ' ' . $odeme->randevu->soyad : 'Serbest Gelir') }}
                    </td>
                    <td>{{ $odeme->hizmet ? $odeme->hizmet->ad : 'Manuel Giriş' }}</td>
                    <td>
                        @php
                            $yontemEtiketleri = [
                                'nakit' => 'Nakit',
                                'kredi_karti' => 'Kredi Kartı',
                                'havale' => 'Havale / EFT',
                                'online' => 'Online Ödeme'
                            ];
                        @endphp
                        {{ $yontemEtiketleri[$odeme->odeme_yontemi] ?? 'Nakit' }}
                    </td>
                    <td class="text-right">{{ number_format($odeme->odenen_tutar, 2, ',', '.') }} ₺</td>
                    <td>
                        @if($odeme->durum === 'beklemede')
                            <span class="badge badge-amber">Beklemede</span>
                        @elseif($odeme->durum === 'kismi_odeme')
                            <span class="badge badge-blue">Kısmi</span>
                        @elseif($odeme->durum === 'odendi')
                            <span class="badge badge-green">Ödendi</span>
                        @elseif($odeme->durum === 'iptal')
                            <span class="badge badge-red">İptal</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #9CA3AF;">Rapor döneminde gelir kaydı bulunmamaktadır.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title" style="page-break-before: auto;">Gider Kayıtları</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Tarih</th>
                <th style="width: 45%;">Gider Açıklaması / Başlık</th>
                <th style="width: 25%;">Kategori</th>
                <th style="width: 15%;" class="text-right">Tutar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($giderler as $gider)
                <tr>
                    <td>{{ $gider->tarih->format('d.m.Y') }}</td>
                    <td>{{ $gider->baslik }}</td>
                    <td>
                        @php
                            $kategoriIsimleri = [
                                'kira' => 'Kira',
                                'personel' => 'Personel',
                                'malzeme' => 'Malzeme',
                                'ekipman' => 'Ekipman',
                                'vergi' => 'Vergi',
                                'sigorta' => 'Sigorta',
                                'diger' => 'Diğer'
                            ];
                        @endphp
                        {{ $kategoriIsimleri[$gider->kategori] ?? 'Diğer' }}
                    </td>
                    <td class="text-right text-red">-{{ number_format($gider->tutar, 2, ',', '.') }} ₺</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #9CA3AF;">Rapor döneminde gider kaydı bulunmamaktadır.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Bu rapor Randevu Ajandam Hekim Yönetim Sistemi tarafından otomatik olarak üretilmiştir. Rapor Üretim Tarihi: {{ date('d.m.Y H:i') }}
    </div>

</body>
</html>
