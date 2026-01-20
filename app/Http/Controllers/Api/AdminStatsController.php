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
     * Get KPI summary for TODAY
     */
    public function stats()
    {
        $startOfToday = Carbon::today();
        $endOfToday = Carbon::today()->endOfDay();

        // Total sales TODAY
        $salesToday = Transaction::whereBetween('created_at', [$startOfToday, $endOfToday])->sum('total');

        // Calculate real profit TODAY
        $profitToday = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$startOfToday, $endOfToday])
            ->sum(DB::raw('(transaction_items.price - products.cost_price) * transaction_items.qty'));

        // Total transactions TODAY
        $transactionsToday = Transaction::whereBetween('created_at', [$startOfToday, $endOfToday])->count();

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
     * Get sales and profit trend per HOUR for Today
     * GET /api/admin/sales-profit-trend
     */
    public function salesProfitTrend()
    {
        $data = [];
        
        // Group by hour for today
        $hourlyStats = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereDate('transactions.created_at', Carbon::today())
            ->select(
                DB::raw('HOUR(transactions.created_at) as hour'),
                DB::raw('SUM(transaction_items.qty * transaction_items.price) as sales'),
                DB::raw('SUM((transaction_items.price - products.cost_price) * transaction_items.qty) as profit')
            )
            ->groupBy('hour')
            ->get()
            ->keyBy('hour');

        // Fill all 24 hours
        for ($i = 0; $i < 24; $i++) {
            $stat = $hourlyStats->get($i);
            
            $data[] = [
                'label' => sprintf('%02d:00', $i),
                'sales' => $stat ? (int) $stat->sales : 0,
                'profit' => $stat ? (int) $stat->profit : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get category sales distribution for TODAY
     * GET /api/admin/category-sales
     */
    public function categorySales()
    {
        try {
            $categorySales = DB::table('transaction_items')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->join('product_tag', 'products.id', '=', 'product_tag.product_id')
                ->join('tags', 'product_tag.tag_id', '=', 'tags.id')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.created_at', Carbon::today())
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
     * Ordered by QUANTITY sold
     */
    public function topProducts(Request $request)
    {
        $period = $request->input('period', 'monthly');
        
        $query = TransactionItem::select(
                'products.name as product_name',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales')
            )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id');

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
            ->orderByDesc('total_qty') // Changed from total_sales
            ->limit(10)
            ->get();

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

    /**
     * Get recent transactions
     * GET /api/admin/recent-transactions
     */
    public function recentTransactions()
    {
        $transactions = Transaction::with(['user:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'transaction_number' => $t->transaction_number,
                    'total' => (int) $t->total,
                    'cashier' => $t->user->name ?? 'System',
                    'time' => $t->created_at->diffForHumans(),
                    'payment_method' => $t->payment_method,
                    'status' => 'Selesai'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
