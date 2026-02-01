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
use Illuminate\Support\Facades\Mail;
use App\Mail\Admin\ReportMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function getTransactionType(Request $request): ?string
    {
        $type = $request->input('transaction_type');
        if (is_string($type) && $type !== '') {
            return $type;
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
            $transactionType = $this->getTransactionType($request);
            
            // Base transaction query with filters
            $transactionQuery = Transaction::query()
                ->whereBetween('created_at', $dates);
            
            if ($transactionType && $transactionType !== 'all') {
                $transactionQuery->where('jenis_transaksi', $transactionType);
            }

            // If tags are present, we calculate sales and transactions based on those tags
            if ($request->tags) {
                $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                
                // Total Sales for these tags only
                $totalSales = (int) TransactionItem::join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                    ->join('produk_tag', 'detail_transaksi.id_produk', '=', 'produk_tag.id_produk')
                    ->whereBetween('transaksi.created_at', $dates)
                    ->whereIn('produk_tag.id_tag', $tagIds)
                    ->when($transactionType && $transactionType !== 'all', function($q) use ($transactionType) {
                        return $q->where('transaksi.jenis_transaksi', $transactionType);
                    })
                    ->sum('detail_transaksi.subtotal');

                // Unique transactions containing these tags
                $totalTransactions = Transaction::join('detail_transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi.id_transaksi')
                    ->join('produk_tag', 'detail_transaksi.id_produk', '=', 'produk_tag.id_produk')
                    ->whereBetween('transaksi.created_at', $dates)
                    ->whereIn('produk_tag.id_tag', $tagIds)
                    ->when($transactionType && $transactionType !== 'all', function($q) use ($transactionType) {
                        return $q->where('transaksi.jenis_transaksi', $transactionType);
                    })
                    ->distinct('transaksi.id_transaksi')
                    ->count('transaksi.id_transaksi');
            } else {
                // Get basic stats from the whole transactions
                $totalSales = (int) $transactionQuery->sum('total_transaksi');
                $totalTransactions = $transactionQuery->count();
            }

            $avgTransaction = $totalTransactions > 0 ? (int) ($totalSales / $totalTransactions) : 0;

            // Calculate profit from transaction items
            $profitQuery = TransactionItem::selectRaw('SUM((detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah) as total_profit')
                ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                ->whereBetween('transaksi.created_at', $dates);

            if ($transactionType && $transactionType !== 'all') {
                $profitQuery->where('transaksi.jenis_transaksi', $transactionType);
            }

            // Tag filter for profit
            if ($request->tags) {
                $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $profitQuery->join('produk_tag', 'detail_transaksi.id_produk', '=', 'produk_tag.id_produk')
                    ->whereIn('produk_tag.id_tag', $tagIds);
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
            $transactionType = $this->getTransactionType($request);

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

            if ($transactionType && $transactionType !== 'all') {
                $trendQuery->where('transaksi.jenis_transaksi', $transactionType);
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

            if ($transactionType && $transactionType !== 'all') {
                $tagProfitQuery->where('transaksi.jenis_transaksi', $transactionType);
            }

            if ($request->tags) {
                $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $tagProfitQuery->whereIn('tag.id_tag', $tagIds);
            }

            $tagProfitData = $tagProfitQuery->get();

            // 3. Transaction Trend (Count) - Filtered by Tags if present
            $trxTrendQuery = Transaction::selectRaw('DATE(transaksi.created_at) as date, COUNT(DISTINCT transaksi.id_transaksi) as count')
                ->whereBetween('transaksi.created_at', $dates);

            if ($transactionType && $transactionType !== 'all') {
                $trxTrendQuery->where('transaksi.jenis_transaksi', $transactionType);
            }

            if ($request->tags) {
                $tagIds = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $trxTrendQuery->join('detail_transaksi', 'transaksi.id_transaksi', '=', 'detail_transaksi.id_transaksi')
                    ->join('produk_tag', 'detail_transaksi.id_produk', '=', 'produk_tag.id_produk')
                    ->whereIn('produk_tag.id_tag', $tagIds);
            }

            $trxTrend = $trxTrendQuery->groupBy(DB::raw('DATE(transaksi.created_at)'))
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
        $transactionType = $this->getTransactionType($request);
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

        if ($transactionType && $transactionType !== 'all') {
            $query->where('transaksi.jenis_transaksi', $transactionType);
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
            $transactionType = $this->getTransactionType($request);
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

            if ($transactionType && $transactionType !== 'all') {
                $query->where('transaksi.jenis_transaksi', $transactionType);
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
     * Send report to owner email
     */
    public function sendToEmail(Request $request): JsonResponse
    {
        try {
            $ownerEmail = config('mail.owner_email');
            if (!$ownerEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email pemilik (OWNER_EMAIL) belum dikonfigurasi'
                ], 422);
            }

            // 1. Get Summary Data
            $summaryRes = $this->getSummary($request);
            $summary = $summaryRes->getData()->data;

            // 2. Get All Items (not paginated for PDF)
            $transactionType = $this->getTransactionType($request);
            $query = TransactionItem::select(
                'transaksi.created_at',
                'transaksi.nomor_transaksi as transaction_number',
                'detail_transaksi.nama_produk as product_name',
                'detail_transaksi.jumlah as qty',
                'detail_transaksi.harga_jual as selling_price',
                'detail_transaksi.harga_pokok as cost_price'
            )
            ->selectRaw('(detail_transaksi.harga_jual - detail_transaksi.harga_pokok) * detail_transaksi.jumlah as profit')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi');

            $dates = $this->getDateRange($request);
            $query->whereBetween('transaksi.created_at', $dates);

            if ($transactionType && $transactionType !== 'all') {
                $query->where('transaksi.jenis_transaksi', $transactionType);
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

            $items = $query->orderBy('transaksi.created_at', 'desc')->get();

            // 3. Prepare Filters for PDF
            $filterLabels = [
                'date_range' => $dates[0]->format('d/m/Y') . ' - ' . $dates[1]->format('d/m/Y'),
                'type' => $transactionType === 'all' || !$transactionType ? 'Semua Tipe' : ucfirst($transactionType),
                'tags' => 'Semua Kategori'
            ];

            if ($request->tags) {
                $tagNames = \App\Models\Tag::whereIn('id_tag', explode(',', $request->tags))->pluck('nama_tag')->toArray();
                $filterLabels['tags'] = implode(', ', $tagNames);
            }

            $pdf = Pdf::loadView('emails.report-pdf', [
                'summary' => (array) $summary,
                'items' => $items,
                'filters' => $filterLabels,
                'charts' => $request->charts, // Receive chart snapshots
                'heatmap' => $request->heatmap // Receive heatmap data
            ]);

            $pdfData = $pdf->output();
            $dateRangeStr = $filterLabels['date_range'];
            $timestamp = date('Ymd-His');
            $pdfFileName = 'laporan-penjualan-' . $timestamp . '.pdf';

            // 4. Generate CSV
            $csvFileName = 'laporan-penjualan-' . $timestamp . '.csv';
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, ['Tanggal', 'Nomor Transaksi', 'Produk', 'Qty', 'Harga Jual', 'Harga Pokok', 'Laba']);
            foreach ($items as $item) {
                fputcsv($handle, [
                    Carbon::parse($item->created_at)->format('d/m/Y H:i'),
                    $item->transaction_number,
                    $item->product_name,
                    $item->qty,
                    (int) $item->selling_price,
                    (int) $item->cost_price,
                    (int) $item->profit
                ]);
            }
            rewind($handle);
            $csvData = stream_get_contents($handle);
            fclose($handle);

            // 5. Send Mail
            Mail::to($ownerEmail)->send(new ReportMail(
                $pdfData, 
                $csvData, 
                $dateRangeStr, 
                $pdfFileName, 
                $csvFileName
            ));

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dikirim ke ' . $ownerEmail
            ]);

        } catch (\Throwable $e) {
            Log::error('ReportController sendToEmail error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
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
