<!DOCTYPE html>
<html lang="tr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Klinik İstatistik ve Raporu</title>
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
        .text-emerald { color: #10B981; }
        .text-rose { color: #EF4444; }
        .text-amber { color: #F59E0B; }
        .text-blue { color: #3B82F6; }
        
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
        .text-center {
            text-align: center;
        }
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
                    <div class="meta-text">Klinik Performans ve Randevu Raporu</div>
                </td>
                <td style="text-align: right;">
                    <div class="report-title">{{ $klinik->ad }}</div>
                    <div class="meta-text-right">Dönem: {{ \Carbon\Carbon::parse($baslangic)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($bitis)->format('d.m.Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Randevu İstatistikleri</div>
    <table class="stats-table">
        <tr>
            <td class="stats-box">
                <div class="stats-label">Toplam Randevu</div>
                <div class="stats-val">{{ $toplamRandevu }}</div>
            </td>
            <td class="stats-box" style="border-left: none;">
                <div class="stats-label">Onaylı/Tamamlanan</div>
                <div class="stats-val text-emerald">
                    {{ ($durumDagilimi['onaylandi'] ?? 0) + ($durumDagilimi['tamamlandi'] ?? 0) }}
                </div>
            </td>
            <td class="stats-box" style="border-left: none;">
                <div class="stats-label">Bekleyen Randevu</div>
                <div class="stats-val text-amber">{{ $durumDagilimi['beklemede'] ?? 0 }}</div>
            </td>
            <td class="stats-box" style="border-left: none;">
                <div class="stats-label">İptal/Gelmedi</div>
                <div class="stats-val text-rose">
                    {{ ($durumDagilimi['iptal'] ?? 0) + ($durumDagilimi['gelmedi'] ?? 0) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Hekim Bazlı Randevu Dağılımı</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Hekim Adı Soyadı</th>
                <th style="width: 30%;" class="text-center">Dönemlik Toplam Randevu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($doktorRandevuSayilari as $doc)
                <tr>
                    <td>{{ $doc['ad_soyad'] }}</td>
                    <td class="text-center font-bold">{{ $doc['adet'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center" style="color: #9CA3AF;">Klinikte aktif hekim bulunmuyor.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">En Çok Tercih Edilen Hizmetler</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50%;">Hizmet Adı</th>
                <th style="width: 20%;" class="text-center">Talep Sayısı</th>
                <th style="width: 30%;" class="text-right">Tahmini Ciro (Hizmet Fiyatı × Adet)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($populerHizmetler as $hizmet)
                <tr>
                    <td>{{ $hizmet->hizmet_ad }}</td>
                    <td class="text-center">{{ $hizmet->adet }} Kez</td>
                    <td class="text-right font-bold text-emerald">₺{{ number_format($hizmet->tahmini_gelir, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #9CA3AF;">Bu dönemde randevu kaydı bulunmuyor.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Bu rapor Randevu Ajandam Klinik Yönetim Paneli tarafından otomatik olarak üretilmiştir. Rapor Tarihi: {{ date('d.m.Y H:i') }}
    </div>

</body>
</html>
