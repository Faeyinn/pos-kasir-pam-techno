<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminStatsController extends Controller
{
    /**
     * Get KPI summary for this month
     */
    public function stats()
    {
        // Use this month instead of today
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Total sales this month
        $salesToday = Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total');

        // Calculate real profit this month
        $profitToday = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('(transaction_items.price - products.cost_price) * transaction_items.qty'));

        // Total transactions this month
        $transactionsToday = Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // Low stock products (stock < 20)
        $lowStockCount = Product::where('stock', '<', 20)
            ->where('is_active', true)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'sales_today' => (int) $salesToday,
                'profit_today' => (int) $profitToday,
                'transactions_today' => $transactionsToday,
                'low_stock_count' => $lowStockCount,
            ]
        ]);
    }

    /**
     * Get sales and profit trend for last 7 days
     * GET /api/admin/sales-profit-trend
     */
    public function salesProfitTrend()
    {
        $days = 7;
        $startDate = Carbon::today()->subDays($days - 1);

        $data = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            $dailySales = Transaction::whereDate('created_at', $date)->sum('total');
            
            // Calculate real profit using cost_price
            $dailyProfit = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->whereDate('transactions.created_at', $date)
                ->sum(DB::raw('(transaction_items.price - products.cost_price) * transaction_items.qty'));

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d M'),
                'sales' => (int) $dailySales,
                'profit' => (int) $dailyProfit,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get category sales distribution
     * GET /api/admin/category-sales
     */
    public function categorySales()
    {
        try {
            // Use tag relationships instead of JSON extraction
            $categorySales = DB::table('transaction_items')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->join('product_tag', 'products.id', '=', 'product_tag.product_id')
                ->join('tags', 'product_tag.tag_id', '=', 'tags.id')
                ->select('tags.name as category', DB::raw('SUM(transaction_items.subtotal) as total_sales'))
                ->groupBy('tags.id', 'tags.name')
                ->orderByDesc('total_sales')
                ->limit(6)
                ->get();

            $labels = [];
            $values = [];
            $total = 0;

            foreach ($categorySales as $item) {
                if ($item->category) {
                    $labels[] = $item->category;
                    $values[] = (int) $item->total_sales;
                    $total += $item->total_sales;
                }
            }

            // If no data, return empty state
            if (empty($labels)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'labels' => [],
                        'values' => [],
                        'total' => 0,
                        'empty' => true
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $values,
                    'total' => (int) $total,
                    'empty' => false
                ]
            ]);
        } catch (\Exception $e) {
            // Return empty state on error instead of failing
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => [],
                    'values' => [],
                    'total' => 0,
                    'empty' => true
                ]
            ]);
        }
    }

    /**
     * Get top selling products
     * GET /api/admin/top-products?period=daily|weekly|monthly
     */
    public function topProducts(Request $request)
    {
        $period = $request->input('period', 'daily');
        
        $query = TransactionItem::select(
                'products.name as product_name',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id');

        // Apply period filter
        switch ($period) {
            case 'weekly':
                $query->where('transactions.created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'monthly':
                $query->where('transactions.created_at', '>=', Carbon::now()->subMonth());
                break;
            case 'daily':
            default:
                $query->whereDate('transactions.created_at', Carbon::today());
                break;
        }

        $topProducts = $query
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        // Add ranking
        $rankedProducts = $topProducts->map(function ($item, $index) {
            return [
                'rank' => $index + 1,
                'product_name' => $item->product_name,
                'total_qty' => (int) $item->total_qty,
                'total_sales' => (int) $item->total_sales,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $rankedProducts,
            'period' => $period,
            'empty' => $rankedProducts->isEmpty()
        ]);
    }
}
