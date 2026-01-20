<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reports page
     */
    public function index(): View
    {
        $tags = Tag::all();
        
        return view('pages.admin.reports', compact('tags'));
    }

    /**
     * Get summary statistics
     */
    public function getSummary(Request $request): JsonResponse
    {
        try {
            $dates = $this->getDateRange($request);
            
            // Base transaction query with filters
            $transactionQuery = Transaction::query()
                ->whereBetween('created_at', $dates);
            
            if ($request->payment_type && $request->payment_type !== 'all') {
                $transactionQuery->where('payment_type', $request->payment_type);
            }

            // Get basic stats
            $totalSales = (int) $transactionQuery->sum('total');
            $totalTransactions = $transactionQuery->count();
            $avgTransaction = $totalTransactions > 0 ? (int) ($totalSales / $totalTransactions) : 0;

            // Calculate profit from transaction items
            $profitQuery = TransactionItem::selectRaw('SUM((transaction_items.price - products.cost_price) * transaction_items.qty) as total_profit')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->whereBetween('transactions.created_at', $dates);

            if ($request->payment_type && $request->payment_type !== 'all') {
                $profitQuery->where('transactions.payment_type', $request->payment_type);
            }

            // Tag filter for profit
            if ($request->tags) {
                $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $profitQuery->join('product_tag', 'products.id', '=', 'product_tag.product_id')
                    ->whereIn('product_tag.tag_id', $tagIds);
            }

            $totalProfit = (int) ($profitQuery->value('total_profit') ?? 0);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_sales' => $totalSales,
                    'total_profit' => $totalProfit,
                    'total_transactions' => $totalTransactions,
                    'avg_transaction' => $avgTransaction
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('ReportController getSummary error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat ringkasan: ' . $e->getMessage(),
                'data' => [
                    'total_sales' => 0,
                    'total_profit' => 0,
                    'total_transactions' => 0,
                    'avg_transaction' => 0
                ]
            ]);
        }
    }

    /**
     * Get chart data
     */
    public function getCharts(Request $request): JsonResponse
    {
        try {
            $dates = $this->getDateRange($request);

            // 1. Sales vs Profit Trend
            $trendQuery = TransactionItem::selectRaw('
                    DATE(transactions.created_at) as date,
                    SUM(transaction_items.subtotal) as sales,
                    SUM((transaction_items.price - products.cost_price) * transaction_items.qty) as profit
                ')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->whereBetween('transactions.created_at', $dates)
                ->groupBy(DB::raw('DATE(transactions.created_at)'))
                ->orderBy('date');

            if ($request->payment_type && $request->payment_type !== 'all') {
                $trendQuery->where('transactions.payment_type', $request->payment_type);
            }

            if ($request->tags) {
                $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $trendQuery->join('product_tag', 'products.id', '=', 'product_tag.product_id')
                    ->whereIn('product_tag.tag_id', $tagIds)
                    ->distinct();
            }

            $trendData = $trendQuery->get();

            // 2. Profit by Tag
            $tagProfitQuery = TransactionItem::selectRaw('
                    tags.name as tag_name,
                    SUM((transaction_items.price - products.cost_price) * transaction_items.qty) as profit
                ')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->join('product_tag', 'products.id', '=', 'product_tag.product_id')
                ->join('tags', 'product_tag.tag_id', '=', 'tags.id')
                ->whereBetween('transactions.created_at', $dates)
                ->groupBy('tags.id', 'tags.name')
                ->orderByDesc('profit')
                ->limit(10);

            if ($request->payment_type && $request->payment_type !== 'all') {
                $tagProfitQuery->where('transactions.payment_type', $request->payment_type);
            }

            $tagProfitData = $tagProfitQuery->get();

            // 3. Transaction Trend (Count)
            $trxTrendQuery = Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereBetween('created_at', $dates);

            if ($request->payment_type && $request->payment_type !== 'all') {
                $trxTrendQuery->where('payment_type', $request->payment_type);
            }

            $trxTrend = $trxTrendQuery->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'sales_profit_trend' => $trendData,
                    'profit_by_tag' => $tagProfitData,
                    'transaction_trend' => $trxTrend
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('ReportController getCharts error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat grafik: ' . $e->getMessage(),
                'data' => [
                    'sales_profit_trend' => [],
                    'profit_by_tag' => [],
                    'transaction_trend' => []
                ]
            ]);
        }
    }

    /**
     * Get detailed report data
     */
    public function getDetail(Request $request): JsonResponse
    {
        $query = TransactionItem::select(
                'transactions.created_at',
                'transactions.transaction_number',
                'products.name as product_name',
                'transaction_items.qty',
                'transaction_items.price as selling_price',
                'products.cost_price',
                'transactions.payment_type'
            )
            ->selectRaw('(transaction_items.price - products.cost_price) * transaction_items.qty as profit')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id');

        // Apply filters
        $dates = $this->getDateRange($request);
        $query->whereBetween('transactions.created_at', $dates);

        if ($request->payment_type && $request->payment_type !== 'all') {
            $query->where('transactions.payment_type', $request->payment_type);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('transactions.transaction_number', 'like', "%{$search}%");
            });
        }

        if ($request->tags) {
            $tagIds = explode(',', $request->tags);
            $query->join('product_tag', 'products.id', '=', 'product_tag.product_id')
                  ->whereIn('product_tag.tag_id', $tagIds)
                  ->distinct();
        }

        // Sorting
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        $sortMap = [
            'date' => 'transactions.created_at',
            'product_name' => 'products.name',
            'qty' => 'transaction_items.qty',
            'price' => 'transaction_items.price',
            'profit' => 'profit'
        ];
        
        $query->orderBy($sortMap[$sortField] ?? $sortField, $sortDirection);

        $data = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Export report to CSV
     */
    public function exportCSV(Request $request): StreamedResponse
    {
        $fileName = 'laporan-penjualan-' . date('Y-m-d-His') . '.csv';

        $response = new StreamedResponse(function () use ($request) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($handle, [
                'Tanggal', 
                'No Transaksi', 
                'Produk', 
                'Qty', 
                'Harga Jual', 
                'Modal', 
                'Total Laba', 
                'Tipe'
            ]);

            $query = TransactionItem::select(
                'transactions.created_at',
                'transactions.transaction_number',
                'products.name as product_name',
                'transaction_items.qty',
                'transaction_items.price as selling_price',
                'products.cost_price',
                'transactions.payment_type'
            )
            ->selectRaw('(transaction_items.price - products.cost_price) * transaction_items.qty as profit')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id');

            // Apply filters
            $dates = $this->getDateRange($request);
            $query->whereBetween('transactions.created_at', $dates);

            if ($request->payment_type && $request->payment_type !== 'all') {
                $query->where('transactions.payment_type', $request->payment_type);
            }
            
            if ($request->tags) {
                $tagIds = explode(',', $request->tags);
                $query->join('product_tag', 'products.id', '=', 'product_tag.product_id')
                      ->whereIn('product_tag.tag_id', $tagIds)
                      ->distinct();
            }

            $query->orderBy('transactions.created_at', 'desc')->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->created_at,
                        $row->transaction_number,
                        $row->product_name,
                        $row->qty,
                        $row->selling_price,
                        $row->cost_price,
                        $row->profit,
                        $row->payment_type,
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    /**
     * Get date range from request
     */
    private function getDateRange(Request $request): array
    {
        if ($request->start_date && $request->end_date) {
            return [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ];
        }
        
        // Default: This Month
        return [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfDay()
        ];
    }
}
