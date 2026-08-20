<!DOCTYPE html>
<html lang="tr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Finansal Rapor</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1F2937;
            line-height: 1.5;
            margin: 0;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #C96A2B;
            padding-bottom: 14px;
            margin-bottom: 22px;
        }
        .header table { width: 100%; }
        .brand {
            font-size: 20px;
            font-weight: bold;
            color: #C96A2B;
            letter-spacing: -0.5px;
        }
        .brand-sub {
            color: #6B7280;
            font-size: 10px;
            margin-top: 2px;
        }
        .report-doctor {
            font-size: 13px;
            font-weight: bold;
            text-align: right;
            color: #111827;
        }
        .report-period {
            color: #6B7280;
            font-size: 10px;
            text-align: right;
            margin-top: 3px;
        }

        /* Section titles */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 8px 0 6px;
            border-bottom: 1px solid #E5E7EB;
            margin-top: 22px;
            margin-bottom: 12px;
        }

        /* Stats grid */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 8px;
        }
        .stats-box {
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 12px 10px;
            text-align: center;
            width: 25%;
            background-color: #FAFAFA;
        }
        .stats-label {
            font-size: 9px;
            color: #6B7280;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.4px;
        }
        .stats-val {
            font-size: 15px;
            font-weight: bold;
            margin-top: 6px;
            letter-spacing: -0.3px;
        }
        .text-green  { color: #059669; }
        .text-red    { color: #DC2626; }
        .text-orange { color: #D97706; }
        .text-slate  { color: #111827; }

        /* Data tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .data-table th {
            background-color: #FAFAFA;
            border-bottom: 2px solid #E5E7EB;
            padding: 8px 10px;
            font-weight: bold;
            text-align: left;
            font-size: 9px;
            color: #4B5563;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table td {
            border-bottom: 1px solid #F3F4F6;
            padding: 9px 10px;
            font-size: 10px;
            color: #1F2937;
        }
        .data-table tbody tr:nth-child(even) td {
            background-color: #FCFCFD;
        }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .muted { color: #9CA3AF; font-style: italic; text-align: center; padding: 22px !important; }

        /* Badges */
        .badge {
            font-size: 8px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }
        .badge-green { background-color: #D1FAE5; color: #065F46; }
        .badge-amber { background-color: #FEF3C7; color: #92400E; }
        .badge-blue  { background-color: #DBEAFE; color: #1E40AF; }
        .badge-red   { background-color: #FEE2E2; color: #991B1B; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 26px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 8px;
            color: #9CA3AF;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="brand">Randevu Ajandam</div>
                    <div class="brand-sub">Hekim Finansal Durum Raporu</div>
                </td>
                <td style="text-align: right;">
                    <div class="report-doctor">{{ $doktor->unvan ? $doktor->unvan . ' ' : '' }}{{ $doktor->ad_soyad }}</div>
                    <div class="report-period">
                        Dönem: {{ $tarihBaslangic->format('d.m.Y') }} — {{ $tarihBitis->format('d.m.Y') }}
                    </div>
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
            <td class="stats-box">
                <div class="stats-label">Toplam Gider</div>
                <div class="stats-val text-red">{{ number_format($toplamGider, 2, ',', '.') }} ₺</div>
            </td>
            <td class="stats-box">
                <div class="stats-label">Net Kâr</div>
                <div class="stats-val {{ $netKar >= 0 ? 'text-slate' : 'text-red' }}">
                    {{ number_format($netKar, 2, ',', '.') }} ₺
                </div>
            </td>
            <td class="stats-box">
                <div class="stats-label">Bekleyen Alacak</div>
                <div class="stats-val text-orange">{{ number_format($toplamTahsilEdilmeyen, 2, ',', '.') }} ₺</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Gelir Kayıtları</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 13%;">Tarih</th>
                <th style="width: 24%;">Hasta</th>
                <th style="width: 25%;">Hizmet</th>
                <th style="width: 14%;">Yöntem</th>
                <th style="width: 12%;" class="text-right">Ödenen</th>
                <th style="width: 12%;">Durum</th>
            </tr>
        </thead>
        <tbody>
            @forelse($odemeler as $odeme)
                <tr>
                    <td>{{ $odeme->odeme_tarihi ? $odeme->odeme_tarihi->format('d.m.Y') : $odeme->created_at->format('d.m.Y') }}</td>
                    <td>{{ $odeme->hasta ? $odeme->hasta->ad_soyad : ($odeme->randevu ? $odeme->randevu->ad . ' ' . $odeme->randevu->soyad : 'Serbest Gelir') }}</td>
                    <td>{{ $odeme->hizmet ? $odeme->hizmet->ad : 'Manuel Giriş' }}</td>
                    <td>
                        @php
                            $yontemEtiketleri = [
                                'nakit'       => 'Nakit',
                                'kredi_karti' => 'Kredi Kartı',
                                'havale'      => 'Havale / EFT',
                                'online'      => 'Online',
                            ];
                        @endphp
                        {{ $yontemEtiketleri[$odeme->odeme_yontemi] ?? 'Nakit' }}
                    </td>
                    <td class="text-right fw-bold text-green">{{ number_format($odeme->odenen_tutar, 2, ',', '.') }} ₺</td>
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
                <tr><td colspan="6" class="muted">Rapor döneminde gelir kaydı bulunmamaktadır.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title" style="page-break-before: auto;">Gider Kayıtları</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 13%;">Tarih</th>
                <th style="width: 45%;">Gider Başlığı / Açıklama</th>
                <th style="width: 24%;">Kategori</th>
                <th style="width: 18%;" class="text-right">Tutar</th>
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
                                'kira'     => 'Kira',
                                'personel' => 'Personel',
                                'malzeme'  => 'Malzeme',
                                'ekipman'  => 'Ekipman',
                                'vergi'    => 'Vergi',
                                'sigorta'  => 'Sigorta',
                                'diger'    => 'Diğer',
                            ];
                        @endphp
                        {{ $kategoriIsimleri[$gider->kategori] ?? 'Diğer' }}
                    </td>
                    <td class="text-right fw-bold text-red">-{{ number_format($gider->tutar, 2, ',', '.') }} ₺</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Rapor döneminde gider kaydı bulunmamaktadır.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Bu rapor Randevu Ajandam Hekim Yönetim Sistemi tarafından otomatik olarak üretilmiştir. Rapor Üretim Tarihi: {{ date('d.m.Y H:i') }}
    </div>

</body>
</html>
