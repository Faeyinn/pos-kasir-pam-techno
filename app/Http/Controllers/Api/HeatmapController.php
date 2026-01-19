<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HeatmapController extends Controller
{
    /**
     * Get purchase frequency heatmap data
     * Shows transaction patterns by day of week and hour
     * 
     * GET /api/admin/heatmap/frequency
     */
    public function getPurchaseFrequency(Request $request)
    {
        try {
            // Default to last 30 days if no dates provided
            $startDate = $request->start_date 
                ? Carbon::parse($request->start_date)
                : Carbon::now()->subDays(30);
                
            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::now();

            // Query: Group by day of week (0=Sunday, 6=Saturday) and hour (0-23)
            $data = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DAYOFWEEK(created_at) - 1 as day_of_week'),
                    DB::raw('HOUR(created_at) as hour'),
                    DB::raw('COUNT(*) as transaction_count')
                )
                ->groupBy('day_of_week', 'hour')
                ->orderBy('day_of_week')
                ->orderBy('hour')
                ->get();

            // Prepare data for line chart (7 series, one per day)
            $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $series = [];
            
            // Initialize series for each day
            for ($day = 0; $day <= 6; $day++) {
                $hourlyData = [];
                
                // Business hours only: 8-22 (15 hours)
                for ($hour = 8; $hour <= 22; $hour++) {
                    $count = $data->where('day_of_week', $day)
                                 ->where('hour', $hour)
                                 ->first();
                    
                    $hourlyData[] = $count ? $count->transaction_count : 0;
                }
                
                $series[] = [
                    'name' => $dayNames[$day],
                    'data' => $hourlyData
                ];
            }

            // Hour labels for x-axis (8-22)
            $hourLabels = [];
            for ($hour = 8; $hour <= 22; $hour++) {
                $hourLabels[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'series' => $series,
                    'hours' => $hourLabels,
                    'total_transactions' => $data->sum('transaction_count'),
                    'period' => [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}
