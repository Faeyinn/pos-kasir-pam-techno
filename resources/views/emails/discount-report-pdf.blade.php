<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Efektivitas Diskon</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #334155; margin: 0; padding: 0px; font-size: 12px; line-height: 1.4; }
        .header { border-bottom: 3px solid #0f172a; padding-bottom: 20px; margin-bottom: 25px; }
        .store-name { font-size: 24px; font-weight: 900; color: #0f172a; margin: 0; text-transform: uppercase; }
        .report-title { font-size: 14px; font-weight: 700; color: #64748b; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .print-date { font-size: 10px; color: #94a3b8; text-align: right; float: right; margin-top: -40px; }
        
        .filter-badge { background: #f1f5f9; padding: 8px 15px; border-radius: 8px; margin-bottom: 25px; display: inline-block; border: 1px solid #e2e8f0; }
        .filter-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 3px; }
        .filter-value { font-size: 11px; font-weight: 700; color: #1e293b; }

        .comparison-grid { width: 100%; border-collapse: separate; border-spacing: 15px; margin: -15px; margin-bottom: 20px; }
        .card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; vertical-align: top; }
        .card-title { font-size: 12px; font-weight: 800; color: #1e293b; margin-bottom: 15px; text-transform: uppercase; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }
        
        .metric-item { margin-bottom: 12px; }
        .metric-label { font-size: 9px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 2px; }
        .metric-value { font-size: 16px; font-weight: 900; color: #0f172a; }
        .metric-sub { font-size: 9px; color: #94a3b8; margin-top: 2px; }
        
        .highlight-green { color: #10b981; }
        .highlight-red { color: #ef4444; }
        
        .conclusion-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 15px; margin-bottom: 30px; }
        .conclusion-title { font-size: 10px; font-weight: 800; color: #1d4ed8; text-transform: uppercase; margin-bottom: 5px; }
        .conclusion-text { font-size: 11px; color: #1e40af; line-height: 1.6; font-weight: 500; }

        .performance-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .performance-table th { background: #f8fafc; color: #475569; font-size: 9px; font-weight: 800; text-transform: uppercase; padding: 10px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .performance-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; vertical-align: middle; }
        
        .roi-badge { padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 10px; display: inline-block; }
        .roi-very-effective { background: #dcfce7; color: #15803d; }
        .roi-good { background: #fef9c3; color: #854d0e; }
        .roi-review { background: #fee2e2; color: #b91c1c; }

        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 10px; color: #94a3b8; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="print-date">
            TANGGAL CETAK<br>
            <strong>{{ date('d F Y') }}</strong>
        </div>
        <h1 class="store-name">PAM TECHNO</h1>
        <div class="report-title">Laporan Efektivitas Diskon</div>
    </div>

    <div class="filter-badge">
        <div class="filter-label">Periode Analisis (30 Hari Terakhir)</div>
        <div class="filter-value">{{ $period['start'] }} - {{ $period['end'] }}</div>
    </div>

    <table class="comparison-grid">
        <tr>
            <td class="card" style="border-left: 4px solid #ef4444; width: 50%;">
                <div class="card-title">Tanpa Diskon</div>
                <div class="metric-item">
                    <div class="metric-label">Rata-rata Belanja</div>
                    <div class="metric-value">Rp {{ number_format($comparison['without_discount']['avg_transaction'], 0, ',', '.') }}</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Total Omzet</div>
                    <div class="metric-value">Rp {{ number_format($comparison['without_discount']['total_revenue'], 0, ',', '.') }}</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Keuntungan Bersih</div>
                    <div class="metric-value">Rp {{ number_format($comparison['without_discount']['total_profit'], 0, ',', '.') }}</div>
                    <div class="metric-sub">({{ $comparison['without_discount']['profit_margin'] }}% margin)</div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Jumlah Transaksi</div>
                    <div class="metric-value">{{ $comparison['without_discount']['transaction_count'] }} Nota</div>
                </div>
            </td>
            <td class="card" style="border-left: 4px solid #10b981; width: 50%;">
                <div class="card-title">Dengan Diskon</div>
                <div class="metric-item">
                    <div class="metric-label">Rata-rata Belanja</div>
                    <div class="metric-value">Rp {{ number_format($comparison['with_discount']['avg_transaction'], 0, ',', '.') }}</div>
                    <div class="metric-sub highlight-{{ $comparison['diff']['avg_transaction'] >= 0 ? 'green' : 'red' }}">
                        {{ $comparison['diff']['avg_transaction'] >= 0 ? '▲' : '▼' }} {{ abs($comparison['diff']['avg_transaction']) }}% dibanding tanpa diskon
                    </div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Total Omzet</div>
                    <div class="metric-value">Rp {{ number_format($comparison['with_discount']['total_revenue'], 0, ',', '.') }}</div>
                    <div class="metric-sub highlight-{{ $comparison['diff']['total_revenue'] >= 0 ? 'green' : 'red' }}">
                        {{ $comparison['diff']['total_revenue'] >= 0 ? '▲' : '▼' }} {{ abs($comparison['diff']['total_revenue']) }}% dibanding tanpa diskon
                    </div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Keuntungan Bersih</div>
                    <div class="metric-value">Rp {{ number_format($comparison['with_discount']['total_profit'], 0, ',', '.') }}</div>
                    <div class="metric-sub">({{ $comparison['with_discount']['profit_margin'] }}% margin)</div>
                    <div class="metric-sub highlight-{{ $comparison['diff']['total_profit'] >= 0 ? 'green' : 'red' }}">
                        {{ $comparison['diff']['total_profit'] >= 0 ? '▲' : '▼' }} {{ abs($comparison['diff']['total_profit']) }}% dibanding tanpa diskon
                    </div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Jumlah Transaksi</div>
                    <div class="metric-value">{{ $comparison['with_discount']['transaction_count'] }} Nota</div>
                    <div class="metric-sub highlight-{{ $comparison['diff']['transaction_count'] >= 0 ? 'green' : 'red' }}">
                        {{ $comparison['diff']['transaction_count'] >= 0 ? '▲' : '▼' }} {{ abs($comparison['diff']['transaction_count']) }}% dibanding tanpa diskon
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="conclusion-box">
        <div class="conclusion-title">Analisis Strategis</div>
        <div class="conclusion-text">
            {!! $comparison['conclusion'] ?? 'Data analisis strategis tidak tersedia.' !!}
        </div>
    </div>

    <div style="page-break-inside: avoid;">
        <div class="filter-label" style="margin-bottom: 10px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 5px;">
            Efektivitas Setiap Diskon
        </div>
        <table class="performance-table">
            <thead>
                <tr>
                    <th>Nama Diskon</th>
                    <th>Periode Berlaku</th>
                    <th>Dipakai</th>
                    <th style="text-align: right;">Potongan</th>
                    <th style="text-align: right;">Revenue</th>
                    <th style="text-align: right;">Laba</th>
                    <th style="text-align: right;">ROI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($performance as $disc)
                <tr>
                    <td style="font-weight: 700;">{{ $disc['name'] }}</td>
                    <td style="font-size: 8px; color: #64748b;">
                        {{ \Carbon\Carbon::parse($disc['start_date'])->translatedFormat('d M Y') }}
                        <br>
                        <span style="color: #cbd5e1; font-size: 7px;">s/d</span>
                        <br>
                        {{ \Carbon\Carbon::parse($disc['end_date'])->translatedFormat('d M Y') }}
                    </td>
                    <td>{{ $disc['usage_count'] }}x</td>
                    <td style="text-align: right;">Rp {{ number_format($disc['total_discount_given'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($disc['total_revenue'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($disc['total_profit'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">
                        @php
                            $roi = (float)($disc['roi_percentage'] ?? 0);
                            $class = 'roi-review';
                            if ($roi > 500) $class = 'roi-very-effective';
                            elseif ($roi >= 200) $class = 'roi-good';
                        @endphp
                        <span class="roi-badge {{ $class }}">{{ $roi }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Laporan ini digenerate secara resmi melalui Sistem POS Kasir PAM Techno.<br>
        &copy; {{ date('Y') }} PAM Techno. Seluruh hak cipta dilindungi.
    </div>
</body>
</html>
