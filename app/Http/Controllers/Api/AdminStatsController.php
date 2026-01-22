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
        $salesToday = Transaction::whereBetween('created_at', [$startOfToday, $endOfToday])->sum('total_transaksi');

        // Calculate real profit TODAY
        $profitToday = TransactionItem::join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
            ->whereBetween('transaksi.created_at', [$startOfToday, $endOfToday])
            ->sum(DB::raw('(detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah'));

        // Total transactions TODAY
        $transactionsToday = Transaction::whereBetween('created_at', [$startOfToday, $endOfToday])->count();

        // Low stock products (stock < 20)
        $lowStockCount = Product::where('stok', '<', 20)
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
        $hourlyStats = TransactionItem::join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
            ->whereDate('transaksi.created_at', Carbon::today())
            ->select(
                DB::raw('HOUR(transaksi.created_at) as hour'),
                DB::raw('SUM(detail_transaksi.subtotal) as sales'),
                DB::raw('SUM((detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah) as profit')
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
            $categorySales = DB::table('detail_transaksi')
                ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
                ->join('produk_tag', 'produk.id_produk', '=', 'produk_tag.id_produk')
                ->join('tag', 'produk_tag.id_tag', '=', 'tag.id_tag')
                ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                ->whereDate('transaksi.created_at', Carbon::today())
                ->select('tag.nama_tag as category', DB::raw('SUM(detail_transaksi.subtotal) as total_sales'))
                ->groupBy('tag.id_tag', 'tag.nama_tag')
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
                'produk.nama_produk as product_name',
                DB::raw('SUM(detail_transaksi.jumlah) as total_qty'),
                DB::raw('SUM(detail_transaksi.subtotal) as total_sales')
            )
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi');

        switch ($period) {
            case 'weekly':
                $query->where('transaksi.created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'monthly':
                $query->where('transaksi.created_at', '>=', Carbon::now()->subMonth());
                break;
            case 'daily':
            default:
                $query->whereDate('transaksi.created_at', Carbon::today());
                break;
        }

        $topProducts = $query
            ->groupBy('produk.id_produk', 'produk.nama_produk')
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
        $transactions = Transaction::with(['user:id,nama'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id_transaksi,
                    'transaction_number' => $t->nomor_transaksi,
                    'total' => (int) $t->total_transaksi,
                    'cashier' => $t->user->nama ?? 'System',
                    'time' => $t->created_at->diffForHumans(),
                    'payment_method' => $t->metode_pembayaran,
                    'status' => 'Selesai'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
