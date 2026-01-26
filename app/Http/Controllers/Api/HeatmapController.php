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

            // Build query with filters
            $query = Transaction::whereBetween('created_at', [$startDate, $endDate]);

            // Filter by transaction type
            if ($request->transaction_type && $request->transaction_type !== 'all') {
                $query->where('jenis_transaksi', $request->transaction_type);
            }

            // Filter by tags (categories)
            if ($request->tags) {
                $tagIds = explode(',', $request->tags);
                $query->whereHas('items.product.tags', function($q) use ($tagIds) {
                    $q->whereIn('tag.id_tag', $tagIds);
                });
            }

            // Query: Group by day of week (0=Sunday, 6=Saturday) and hour (0-23)
            $data = $query->select(
                    DB::raw('DAYOFWEEK(created_at) - 1 as day_of_week'),
                    DB::raw('HOUR(created_at) as hour'),
                    DB::raw('COUNT(*) as transaction_count')
                )
                ->groupBy('day_of_week', 'hour')
                ->orderBy('day_of_week')
                ->orderBy('hour')
                ->get();

            // Prepare heatmap matrix
            $heatmap = [];
            $maxValue = 0;
            $peakDay = null;
            $peakHour = null;
            
            for ($day = 0; $day <= 6; $day++) {
                $heatmap[$day] = [];
                for ($hour = 0; $hour <= 23; $hour++) {
                    $count = $data->where('day_of_week', $day)
                                 ->where('hour', $hour)
                                 ->first();
                    
                    $val = $count ? (int) $count->transaction_count : 0;
                    $heatmap[$day][$hour] = $val;
                    
                    if ($val > $maxValue) {
                        $maxValue = $val;
                        $peakDay = $day;
                        $peakHour = $hour;
                    }
                }
            }

            // Original series format for backward compatibility if needed
            $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $series = [];
            for ($day = 0; $day <= 6; $day++) {
                $hourlyData = [];
                for ($hour = 0; $hour <= 23; $hour++) {
                    $hourlyData[] = $heatmap[$day][$hour];
                }
                $series[] = [
                    'name' => $dayNames[$day],
                    'data' => $hourlyData
                ];
            }

            // Hour labels for x-axis (0-23)
            $hourLabels = [];
            for ($hour = 0; $hour <= 23; $hour++) {
                $hourLabels[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'heatmap' => $heatmap,
                    'max_value' => $maxValue,
                    'peak_day' => $peakDay,
                    'peak_hour' => $peakHour,
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
