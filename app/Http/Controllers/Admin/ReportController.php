<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    private function getPaymentMethod(Request $request): ?string
    {
        $method = $request->input('payment_method');
        if (is_string($method) && $method !== '') {
            return $method;
        }

        $legacy = $request->input('payment_type');
        if (is_string($legacy) && $legacy !== '') {
            return $legacy;
        }

        return null;
    }
    /**
     * Display reports page
     */
    public function index(): View
    {
        $tags = Tag::orderBy('nama_tag')
            ->get()
            ->map(fn (Tag $t) => [
                'id' => $t->id_tag,
                'name' => $t->nama_tag,
                'slug' => $t->slug,
                'color' => $t->color,
            ])
            ->values();
        
        return view('pages.admin.reports', compact('tags'));
    }

    /**
     * Get summary statistics
     */
    public function getSummary(Request $request): JsonResponse
    {
        try {
            $dates = $this->getDateRange($request);
            $paymentMethod = $this->getPaymentMethod($request);
            
            // Base transaction query with filters
            $transactionQuery = Transaction::query()
                ->whereBetween('created_at', $dates);
            
            if ($paymentMethod && $paymentMethod !== 'all') {
                $transactionQuery->where('metode_pembayaran', $paymentMethod);
            }

            // Get basic stats
            $totalSales = (int) $transactionQuery->sum('total_transaksi');
            $totalTransactions = $transactionQuery->count();
            $avgTransaction = $totalTransactions > 0 ? (int) ($totalSales / $totalTransactions) : 0;

            // Calculate profit from transaction items
            $profitQuery = TransactionItem::selectRaw('SUM((detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah) as total_profit')
                ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                ->whereBetween('transaksi.created_at', $dates);

            if ($paymentMethod && $paymentMethod !== 'all') {
                $profitQuery->where('transaksi.metode_pembayaran', $paymentMethod);
            }

            // Tag filter for profit
            if ($request->tags) {
                $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $profitQuery->whereExists(function ($q) use ($tagIds) {
                    $q->select(DB::raw(1))
                        ->from('produk_tag')
                        ->whereColumn('produk_tag.id_produk', 'detail_transaksi.id_produk')
                        ->whereIn('produk_tag.id_tag', $tagIds);
                });
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
            $paymentMethod = $this->getPaymentMethod($request);

            // 1. Sales vs Profit Trend
            $trendQuery = TransactionItem::selectRaw('
                    DATE(transaksi.created_at) as date,
                    SUM(detail_transaksi.subtotal) as sales,
                    SUM((detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah) as profit
                ')
                ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                ->whereBetween('transaksi.created_at', $dates)
                ->groupBy(DB::raw('DATE(transaksi.created_at)'))
                ->orderBy('date');

            if ($paymentMethod && $paymentMethod !== 'all') {
                $trendQuery->where('transaksi.metode_pembayaran', $paymentMethod);
            }

            if ($request->tags) {
                $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $trendQuery->whereExists(function ($q) use ($tagIds) {
                    $q->select(DB::raw(1))
                        ->from('produk_tag')
                        ->whereColumn('produk_tag.id_produk', 'detail_transaksi.id_produk')
                        ->whereIn('produk_tag.id_tag', $tagIds);
                });
            }

            $trendData = $trendQuery->get();

            // 2. Profit by Tag
            $tagProfitQuery = TransactionItem::selectRaw('
                    tag.nama_tag as tag_name,
                    SUM((detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah) as profit
                ')
                ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                ->join('produk_tag', 'detail_transaksi.id_produk', '=', 'produk_tag.id_produk')
                ->join('tag', 'produk_tag.id_tag', '=', 'tag.id_tag')
                ->whereBetween('transaksi.created_at', $dates)
                ->groupBy('tag.id_tag', 'tag.nama_tag')
                ->orderByDesc('profit')
                ->limit(10);

            if ($paymentMethod && $paymentMethod !== 'all') {
                $tagProfitQuery->where('transaksi.metode_pembayaran', $paymentMethod);
            }

            $tagProfitData = $tagProfitQuery->get();

            // 3. Transaction Trend (Count)
            $trxTrendQuery = Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereBetween('created_at', $dates);

            if ($paymentMethod && $paymentMethod !== 'all') {
                $trxTrendQuery->where('metode_pembayaran', $paymentMethod);
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
        $paymentMethod = $this->getPaymentMethod($request);
        $query = TransactionItem::select(
            'transaksi.created_at',
            'transaksi.nomor_transaksi as transaction_number',
            'detail_transaksi.nama_produk as product_name',
            'detail_transaksi.jumlah as qty',
            'detail_transaksi.harga_jual as selling_price',
            'detail_transaksi.harga_pokok as cost_price',
            'transaksi.metode_pembayaran as payment_method',
            'transaksi.metode_pembayaran as payment_type'
            )
            ->selectRaw('(detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah as profit')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi');

        // Apply filters
        $dates = $this->getDateRange($request);
        $query->whereBetween('transaksi.created_at', $dates);

        if ($paymentMethod && $paymentMethod !== 'all') {
            $query->where('transaksi.metode_pembayaran', $paymentMethod);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('detail_transaksi.nama_produk', 'like', "%{$search}%")
                  ->orWhere('transaksi.nomor_transaksi', 'like', "%{$search}%");
            });
        }

        if ($request->tags) {
            $tagIds = explode(',', $request->tags);
            $query->whereExists(function ($q) use ($tagIds) {
                $q->select(DB::raw(1))
                    ->from('produk_tag')
                    ->whereColumn('produk_tag.id_produk', 'detail_transaksi.id_produk')
                    ->whereIn('produk_tag.id_tag', $tagIds);
            });
        }

        // Sorting
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        $sortMap = [
            'date' => 'transaksi.created_at',
            'product_name' => 'detail_transaksi.nama_produk',
            'qty' => 'detail_transaksi.jumlah',
            'price' => 'detail_transaksi.harga_jual',
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
            $paymentMethod = $this->getPaymentMethod($request);
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
                'Metode Pembayaran'
            ]);

            $query = TransactionItem::select(
                'transaksi.created_at',
                'transaksi.nomor_transaksi as transaction_number',
                'detail_transaksi.nama_produk as product_name',
                'detail_transaksi.jumlah as qty',
                'detail_transaksi.harga_jual as selling_price',
                'detail_transaksi.harga_pokok as cost_price',
                'transaksi.metode_pembayaran as payment_method',
                'transaksi.metode_pembayaran as payment_type'
            )
            ->selectRaw('(detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah as profit')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi');

            // Apply filters
            $dates = $this->getDateRange($request);
            $query->whereBetween('transaksi.created_at', $dates);

            if ($paymentMethod && $paymentMethod !== 'all') {
                $query->where('transaksi.metode_pembayaran', $paymentMethod);
            }
            
            if ($request->tags) {
                $tagIds = explode(',', $request->tags);
                $query->whereExists(function ($q) use ($tagIds) {
                    $q->select(DB::raw(1))
                        ->from('produk_tag')
                        ->whereColumn('produk_tag.id_produk', 'detail_transaksi.id_produk')
                        ->whereIn('produk_tag.id_tag', $tagIds);
                });
            }

            $query->orderBy('transaksi.created_at', 'desc')->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->created_at,
                        $row->transaction_number,
                        $row->product_name,
                        $row->qty,
                        $row->selling_price,
                        $row->cost_price,
                        $row->profit,
                        $row->payment_method ?? $row->payment_type,
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
