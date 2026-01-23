<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DiscountAnalyticsController extends Controller
{
    /**
     * Get discount analytics data
     * GET /api/admin/discounts/analytics
     */
    public function index(Request $request)
    {
        try {
            // Default to last 30 days
            $startDate = $request->start_date 
                ? Carbon::parse($request->start_date)
                : Carbon::now()->subDays(30);
                
            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::now();

            $trxBaseQuery = DB::table('transaksi')
                ->whereBetween('created_at', [$startDate, $endDate]);

            $withoutTrx = (clone $trxBaseQuery)
                ->where('diskon', '=', 0)
                ->selectRaw('
                    COUNT(*) as transaction_count,
                    AVG(total_transaksi) as avg_transaction,
                    SUM(total_transaksi) as total_revenue,
                    SUM(diskon) as total_discount_given
                ')
                ->first();

            $withTrx = (clone $trxBaseQuery)
                ->where('diskon', '>', 0)
                ->selectRaw('
                    COUNT(*) as transaction_count,
                    AVG(total_transaksi) as avg_transaction,
                    SUM(total_transaksi) as total_revenue,
                    SUM(diskon) as total_discount_given
                ')
                ->first();

            $withoutRawProfit = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                ->whereBetween('transaksi.created_at', [$startDate, $endDate])
                ->where('transaksi.diskon', '=', 0)
                ->sum(DB::raw('(detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah'));

            $withRawProfit = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                ->whereBetween('transaksi.created_at', [$startDate, $endDate])
                ->where('transaksi.diskon', '>', 0)
                ->sum(DB::raw('(detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah'));

            $withoutDiscount = (object) [
                'transaction_count' => (int) ($withoutTrx->transaction_count ?? 0),
                'avg_transaction' => (float) ($withoutTrx->avg_transaction ?? 0),
                'total_revenue' => (float) ($withoutTrx->total_revenue ?? 0),
                'total_profit' => (float) $withoutRawProfit,
            ];

            $withDiscountNetProfit = (float) $withRawProfit - (float) ($withTrx->total_discount_given ?? 0);
            $withDiscount = (object) [
                'transaction_count' => (int) ($withTrx->transaction_count ?? 0),
                'avg_transaction' => (float) ($withTrx->avg_transaction ?? 0),
                'total_revenue' => (float) ($withTrx->total_revenue ?? 0),
                'total_profit' => (float) $withDiscountNetProfit,
            ];

            // Calculate differences - Comparison is "With Discount" vs "Without Discount"
            $avgTransDiff = $withoutDiscount->avg_transaction > 0
                ? round((($withDiscount->avg_transaction - $withoutDiscount->avg_transaction) / $withoutDiscount->avg_transaction) * 100, 1)
                : ($withDiscount->avg_transaction > 0 ? 100 : 0);

            $revenueDiff = $withoutDiscount->total_revenue > 0
                ? round((($withDiscount->total_revenue - $withoutDiscount->total_revenue) / $withoutDiscount->total_revenue) * 100, 1)
                : ($withDiscount->total_revenue > 0 ? 100 : 0);

            $profitDiff = $withoutDiscount->total_profit > 0
                ? round((($withDiscount->total_profit - $withoutDiscount->total_profit) / $withoutDiscount->total_profit) * 100, 1)
                : ($withDiscount->total_profit > 0 ? 100 : 0);

            $transCountDiff = $withoutDiscount->transaction_count > 0
                ? round((($withDiscount->transaction_count - $withoutDiscount->transaction_count) / $withoutDiscount->transaction_count) * 100, 1)
                : ($withDiscount->transaction_count > 0 ? 100 : 0);

            // Calculate profit margins
            $withoutMargin = $withoutDiscount->total_revenue > 0
                ? round(($withoutDiscount->total_profit / $withoutDiscount->total_revenue) * 100, 1)
                : 0;

            $withMargin = $withDiscount->total_revenue > 0
                ? round(($withDiscount->total_profit / $withDiscount->total_revenue) * 100, 1)
                : 0;

            // Calculate per-discount performance
            $performanceData = DB::table('transaksi')
                ->join('diskon', 'transaksi.id_diskon', '=', 'diskon.id_diskon')
                ->whereBetween('transaksi.created_at', [$startDate, $endDate])
                ->selectRaw('
                    diskon.id_diskon,
                    diskon.nama_diskon,
                    COUNT(*) as usage_count,
                    SUM(transaksi.diskon) as total_discount_given,
                    SUM(transaksi.total_transaksi) as total_revenue
                ')
                ->groupBy('diskon.id_diskon', 'diskon.nama_diskon')
                ->get();

            $performance = $performanceData->map(function($p) use ($startDate, $endDate) {
                // Calculate raw profit for transactions using this specific discount
                $rawProfit = DB::table('detail_transaksi')
                    ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                    ->where('transaksi.id_diskon', $p->id_diskon)
                    ->whereBetween('transaksi.created_at', [$startDate, $endDate])
                    ->sum(DB::raw('(detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah'));

                $netProfit = (float) $rawProfit - (float) $p->total_discount_given;
                
                // ROI = (Revenue / Discount Given) * 100
                $roi = $p->total_discount_given > 0
                    ? round(($p->total_revenue / $p->total_discount_given) * 100, 1)
                    : 0;

                return [
                    'name' => $p->nama_diskon,
                    'usage_count' => (int) $p->usage_count,
                    'total_discount_given' => (int) $p->total_discount_given,
                    'total_revenue' => (int) $p->total_revenue,
                    'total_profit' => (int) $netProfit,
                    'roi_percentage' => $roi
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'comparison' => [
                        'without_discount' => [
                            'avg_transaction' => (int) ($withoutDiscount->avg_transaction ?? 0),
                            'total_revenue' => (int) ($withoutDiscount->total_revenue ?? 0),
                            'total_profit' => (int) ($withoutDiscount->total_profit ?? 0),
                            'profit_margin' => $withoutMargin,
                            'transaction_count' => (int) ($withoutDiscount->transaction_count ?? 0)
                        ],
                        'with_discount' => [
                            'avg_transaction' => (int) ($withDiscount->avg_transaction ?? 0),
                            'total_revenue' => (int) ($withDiscount->total_revenue ?? 0),
                            'total_profit' => (int) ($withDiscount->total_profit ?? 0),
                            'profit_margin' => $withMargin,
                            'transaction_count' => (int) ($withDiscount->transaction_count ?? 0)
                        ],
                        'diff' => [
                            'avg_transaction' => $avgTransDiff,
                            'total_revenue' => $revenueDiff,
                            'total_profit' => $profitDiff,
                            'transaction_count' => $transCountDiff
                        ]
                    ],
                    'performance' => $performance
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Discount Analytics Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat analytics: ' . $e->getMessage()
            ], 500);
        }
    }
}
