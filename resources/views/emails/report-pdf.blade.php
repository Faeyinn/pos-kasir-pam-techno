<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan PAM Techno</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        
        /* Premium Header */
        .header-container {
            border-bottom: 4px solid #0f172a;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header-table {
            width: 100%;
        }
        .title-main {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .title-sub {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .print-date-label {
            font-size: 9px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            text-align: right;
        }
        .print-date-value {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
        }

        /* Active Filter Badge */
        .filter-badge {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 25px;
        }
        .filter-label {
            font-size: 9px;
            font-weight: 900;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .filter-content {
            font-size: 11px;
            font-weight: bold;
            color: #334155;
        }

        /* Summary Grid */
        .summary-table {
            width: 100%;
            border-spacing: 10px;
            margin: -10px;
            margin-bottom: 20px;
        }
        .summary-card {
            background-color: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 15px;
            width: 25%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            vertical-align: top;
        }
        .card-label {
            font-size: 9px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .card-value {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }
        
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 12px 10px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }
        .data-table td {
            padding: 10px;
            font-size: 10px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="title-main">LAPORAN ANALISIS PENJUALAN</h1>
                    <div class="title-sub">PAM TECHNO • POS ANALYTICS SYSTEM</div>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <div class="print-date-label">Tanggal Cetak</div>
                    <div class="print-date-value">{{ now()->translatedFormat('d F Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Active Filter Badge -->
    <div class="filter-badge">
        <div class="filter-label">
            <svg width="10" height="10" style="vertical-align: middle; margin-right: 3px;">
                <path d="M1 1h8l-3 4v4l-2-2V5L1 1z" fill="none" stroke="#6366f1" stroke-width="1.5"/>
            </svg>
            Filter Aktif
        </div>
        <div class="filter-content">
            {{ $filters['date_range'] }} • {{ $filters['tags'] }} • {{ $filters['type'] }}
        </div>
    </div>

    <!-- Summary Cards -->
    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <div class="card-label">Total Penjualan</div>
                <div class="card-value">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card">
                <div class="card-label">Total Laba</div>
                <div class="card-value" style="color: #10b981;">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card">
                <div class="card-label">Total Transaksi</div>
                <div class="card-value">{{ number_format($summary['total_transactions'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td class="summary-card">
                <div class="card-label">Rata-rata Transaksi</div>
                <div class="card-value">Rp {{ number_format($summary['avg_transaction'] ?? 0, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Charts Section -->
    @if(isset($charts) && (isset($charts['salesProfit']) || isset($charts['profitTag'])))
    <div style="margin-bottom: 25px;">
        <div class="filter-label" style="margin-bottom: 10px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 5px;">
            Analisis Grafik
        </div>
        <table style="width: 100%; border-spacing: 15px; margin: -15px;">
            <tr>
                @if(isset($charts['salesProfit']))
                <td style="width: 60%; background: #ffffff; border-radius: 12px; border: 1px solid #f1f5f9; padding: 15px; text-align: center;">
                    <div class="card-label" style="text-align: left; margin-bottom: 10px;">Tren Penjualan & Laba</div>
                    <img src="{{ $charts['salesProfit'] }}" style="width: 100%; height: auto; max-height: 200px;">
                </td>
                @endif
                
                @if(isset($charts['profitTag']))
                <td style="width: 40%; background: #ffffff; border-radius: 12px; border: 1px solid #f1f5f9; padding: 15px; text-align: center;">
                    <div class="card-label" style="text-align: left; margin-bottom: 10px;">Distribusi Laba / Kategori</div>
                    <img src="{{ $charts['profitTag'] }}" style="width: 100%; height: auto; max-height: 200px;">
                </td>
                @endif
            </tr>
        </table>

        @if(isset($charts['trxTrend']) || isset($charts['hourlyPattern']))
        <table style="width: 100%; border-spacing: 15px; margin: -15px; margin-top: 5px;">
            <tr>
                @if(isset($charts['trxTrend']))
                <td style="width: 50%; background: #ffffff; border-radius: 12px; border: 1px solid #f1f5f9; padding: 15px; text-align: center;">
                    <div class="card-label" style="text-align: left; margin-bottom: 10px;">Volume Transaksi Harian</div>
                    <img src="{{ $charts['trxTrend'] }}" style="width: 100%; height: auto; max-height: 180px;">
                </td>
                @endif
                
                @if(isset($charts['hourlyPattern']))
                <td style="width: 50%; background: #ffffff; border-radius: 12px; border: 1px solid #f1f5f9; padding: 15px; text-align: center;">
                    <div class="card-label" style="text-align: left; margin-bottom: 10px;">Intensitas Jam Belanja</div>
                    <img src="{{ $charts['hourlyPattern'] }}" style="width: 100%; height: auto; max-height: 180px;">
                </td>
                @endif
            </tr>
        </table>
        @endif
    </div>
    @endif

    <!-- Heatmap Section -->
    @if(isset($heatmap) && isset($heatmap['heatmap']))
    <div style="margin-bottom: 25px; page-break-inside: avoid;">
        <div class="filter-label" style="margin-bottom: 10px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 5px;">
            Analisis Intensitas Transaksi (24 Jam)
        </div>
        
        <table style="width: 100%; border-collapse: separate; border-spacing: 2px;">
            <thead>
                <tr>
                    <th style="width: 30px; background: transparent; border: none;"></th>
                    @for($h = 0; $h < 24; $h++)
                    <th style="font-size: 7px; color: #94a3b8; text-align: center; font-weight: normal; padding-bottom: 4px;">
                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                    </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @php
                    $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                    $maxValue = $heatmap['max_value'] ?? 1;
                @endphp
                @foreach($days as $dayIndex => $dayName)
                <tr>
                    <td style="font-size: 8px; font-weight: bold; color: #94a3b8; text-transform: uppercase; text-align: right; padding-right: 5px;">
                        {{ $dayName }}
                    </td>
                    @for($h = 0; $h < 24; $h++)
                        @php
                            $val = $heatmap['heatmap'][$dayIndex][$h] ?? 0;
                            $percent = $maxValue > 0 ? $val / $maxValue : 0;
                            $bgColor = '#f8fafc'; // Default/Sepi
                            if ($val > 0) {
                                if ($percent < 0.25) $bgColor = '#dcfce7'; // green-100
                                elseif ($percent < 0.5) $bgColor = '#86efac'; // green-300
                                elseif ($percent < 0.75) $bgColor = '#22c55e'; // green-500
                                else $bgColor = '#15803d'; // green-700
                            }
                        @endphp
                        <td style="background-color: {{ $bgColor }}; height: 12px; border-radius: 2px; border: 1px solid rgba(0,0,0,0.03);"></td>
                    @endfor
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Heatmap Legend & Peak Info -->
        <table style="width: 100%; margin-top: 15px;">
            <tr>
                <td style="width: 40%;">
                    <div style="font-size: 8px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">Skala Intensitas</div>
                    <table style="border-spacing: 4px; margin-left: -4px;">
                        <tr>
                            <td style="font-size: 7px; color: #94a3b8; font-weight: bold; padding-right: 5px;">SEPI</td>
                            <td style="width: 10px; height: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1px;"></td>
                            <td style="width: 10px; height: 10px; background: #dcfce7; border-radius: 1px;"></td>
                            <td style="width: 10px; height: 10px; background: #86efac; border-radius: 1px;"></td>
                            <td style="width: 10px; height: 10px; background: #22c55e; border-radius: 1px;"></td>
                            <td style="width: 10px; height: 10px; background: #15803d; border-radius: 1px;"></td>
                            <td style="font-size: 7px; color: #94a3b8; font-weight: bold; padding-left: 5px;">RAMAI</td>
                        </tr>
                    </table>
                </td>
                @if(isset($heatmap['peak_hour']))
                <td style="width: 60%; vertical-align: bottom;">
                    <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 8px 12px;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 30px;">
                                    <div style="width: 24px; height: 24px; background: #eef2ff; border-radius: 6px; text-align: center; line-height: 24px; color: #6366f1; font-weight: bold;">↑</div>
                                </td>
                                <td>
                                    <div style="font-size: 8px; font-weight: bold; color: #94a3b8; text-transform: uppercase;">Prime Time (Puncak)</div>
                                    <div style="font-size: 10px; font-weight: 800; color: #1e293b;">
                                        Jam {{ str_pad($heatmap['peak_hour'], 2, '0', STR_PAD_LEFT) }}:00 di hari {{ ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$heatmap['peak_day']] }}
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="background: #ecfdf5; color: #10b981; font-size: 8px; font-weight: 900; padding: 2px 6px; border-radius: 10px; border: 1px solid #d1fae5; display: inline-block;">
                                        {{ $heatmap['max_value'] }} TRX
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                @endif
            </tr>
        </table>
    </div>
    @endif

    <div class="footer">
        Laporan ini digenerate secara resmi melalui Sistem POS Kasir PAM Techno.<br>
        &copy; {{ date('Y') }} PAM Techno. Seluruh hak cipta dilindungi.
    </div>
</body>
</html>
