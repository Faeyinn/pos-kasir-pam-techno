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

            // Base query for transactions with profit calculation
            $baseQuery = DB::table('transactions')
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->whereBetween('transactions.created_at', [$startDate, $endDate]);

            // Get transactions WITHOUT discount
            $withoutDiscount = (clone $baseQuery)
                ->whereNull('transactions.discount_id')
                ->selectRaw('
                    COUNT(DISTINCT transactions.id) as transaction_count,
                    AVG(transactions.total) as avg_transaction,
                    SUM(transaction_items.qty * transaction_items.price) as total_revenue,
                    SUM((transaction_items.price - products.cost_price) * transaction_items.qty) as total_profit
                ')
                ->first();

            // Get transactions WITH discount
            $withDiscount = (clone $baseQuery)
                ->whereNotNull('transactions.discount_id')
                ->selectRaw('
                    COUNT(DISTINCT transactions.id) as transaction_count,
                    AVG(transactions.total) as avg_transaction,
                    SUM(transaction_items.qty * transaction_items.price) as total_revenue,
                    SUM((transaction_items.price - products.cost_price) * transaction_items.qty) as total_profit
                ')
                ->first();

            // Adjust profit for "With Discount" to account for transaction-level discount
            $totalTransactionDiscount = DB::table('transactions')
                ->whereNotNull('discount_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('discount_amount');
            
            if ($withDiscount) {
                $withDiscount->total_profit = ($withDiscount->total_profit ?? 0) - $totalTransactionDiscount;
            }

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

            // Get per-discount performance
            $performance = DB::table('discounts')
                ->join('transactions', 'discounts.id', '=', 'transactions.discount_id')
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->whereBetween('transactions.created_at', [$startDate, $endDate])
                ->select(
                    'discounts.id',
                    'discounts.name',
                    'discounts.type',
                    'discounts.value',
                    DB::raw('COUNT(DISTINCT transactions.id) as usage_count'),
                    DB::raw('SUM(transactions.discount_amount) as total_discount_given'),
                    DB::raw('SUM(transaction_items.qty * transaction_items.price) as total_revenue'),
                    DB::raw('SUM((transaction_items.price - products.cost_price) * transaction_items.qty) as raw_profit')
                )
                ->groupBy('discounts.id', 'discounts.name', 'discounts.type', 'discounts.value')
                ->get()
                ->map(function ($item) {
                    // Net profit = raw profit from items - transaction level discount
                    $netProfit = $item->raw_profit - $item->total_discount_given;
                    
                    $roi = $item->total_discount_given > 0
                        ? round(($item->total_revenue / $item->total_discount_given) * 100, 0)
                        : 0;

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'type' => $item->type,
                        'value' => $item->value,
                        'usage_count' => (int) $item->usage_count,
                        'total_discount_given' => (int) $item->total_discount_given,
                        'total_revenue' => (int) $item->total_revenue,
                        'total_profit' => (int) $netProfit,
                        'roi_percentage' => $roi
                    ];
                })
                ->sortByDesc('roi_percentage')
                ->values();

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
