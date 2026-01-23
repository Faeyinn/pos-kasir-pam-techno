<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\ProdukSatuan;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Services\DiscountService;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Transaction::with(['user', 'items'])
            ->orderBy('created_at', 'desc');

        // Filter by user (kasir)
        if ($request->has('user_id')) {
            $query->where('id_user', $request->user_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $transactions = $query->get();

        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions)->resolve()
        ]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        
        try {
            $itemsForCreate = [];
            $cartItemsForDiscount = [];
            $totalBelanja = 0;

            foreach ($validated['items'] as $item) {
                $produk = Product::findOrFail($item['id_produk']);

                $satuan = ProdukSatuan::where('id_satuan', $item['id_satuan'])
                    ->where('id_produk', $produk->id_produk)
                    ->firstOrFail();

                $jumlah = (int) $item['jumlah'];
                $qtyBaseToDeduct = $jumlah * (int) $satuan->jumlah_per_satuan;

                if ($produk->stok < $qtyBaseToDeduct) {
                    throw new \Exception("Stok {$produk->nama_produk} tidak mencukupi");
                }

                $hargaJual = (int) $satuan->harga_jual;
                $subtotalItem = $jumlah * $hargaJual;

                $totalBelanja += $subtotalItem;

                $itemsForCreate[] = [
                    'id_produk' => $produk->id_produk,
                    'id_satuan' => $satuan->id_satuan,
                    'nama_produk' => $produk->nama_produk,
                    'nama_satuan' => $satuan->nama_satuan,
                    'jumlah_per_satuan' => (int) $satuan->jumlah_per_satuan,
                    'jumlah' => $jumlah,
                    'harga_pokok' => (int) $satuan->harga_pokok,
                    'harga_jual' => $hargaJual,
                    'subtotal' => $subtotalItem,
                    'qty_base_to_deduct' => $qtyBaseToDeduct,
                ];

                $cartItemsForDiscount[] = [
                    'product_id' => $produk->id_produk,
                    'qty' => $jumlah,
                    'price' => $hargaJual,
                ];
            }

            // APPLY DISCOUNT
            $discountService = app(DiscountService::class);
            $cartItems = collect($cartItemsForDiscount);
            $discountData = $discountService->findApplicableDiscount($cartItems);

            $discountAmount = (int) ($discountData['amount'] ?? 0);
            $discount = $discountData['discount'];

            // Calculate final total
            $discountAmount = min($discountAmount, $totalBelanja);
            $totalTransaksi = $totalBelanja - $discountAmount;
            $kembalian = (int) $validated['jumlah_dibayar'] - $totalTransaksi;

            if ($kembalian < 0) {
                throw new \Exception('Jumlah uang yang diterima kurang dari total');
            }

            // Create transaction
            $jenisTransaksi = $validated['jenis_transaksi'] ?? null;
            if (!$jenisTransaksi) {
                $jenisTransaksi = collect($itemsForCreate)->contains(function ($it) {
                    return (int) $it['qty_base_to_deduct'] > (int) $it['jumlah'];
                }) ? 'grosir' : 'eceran';
            }

            $transaction = Transaction::create([
                'nomor_transaksi' => Transaction::generateTransactionNumber(),
                'id_user' => auth()->id() ?? 1, // Default to user ID 1 if not authenticated
                'jenis_transaksi' => $jenisTransaksi,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'total_belanja' => $totalBelanja,
                'diskon' => $discountAmount,
                'id_diskon' => $discount?->id_diskon,
                'total_transaksi' => $totalTransaksi,
                'jumlah_dibayar' => (int) $validated['jumlah_dibayar'],
                'kembalian' => $kembalian,
            ]);

            // Create transaction items and update stock
            foreach ($itemsForCreate as $item) {
                TransactionItem::create([
                    'id_transaksi' => $transaction->id_transaksi,
                    'id_produk' => $item['id_produk'],
                    'id_satuan' => $item['id_satuan'],
                    'nama_produk' => $item['nama_produk'],
                    'nama_satuan' => $item['nama_satuan'],
                    'jumlah_per_satuan' => $item['jumlah_per_satuan'],
                    'jumlah' => $item['jumlah'],
                    'harga_pokok' => $item['harga_pokok'],
                    'harga_jual' => $item['harga_jual'],
                    'subtotal' => $item['subtotal'],
                ]);

                Product::where('id_produk', $item['id_produk'])->decrement('stok', $item['qty_base_to_deduct']);
            }

            // Log discount if applied
            if ($discount) {
                $discountService->logDiscountApplication(
                    $discount,
                    $discountAmount,
                    $transaction->nomor_transaksi
                );
            }

            DB::commit();

            // Load relationships
            $transaction->load(['user', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'data' => (new TransactionResource($transaction))->resolve(),
                'discount_applied' => $discount ? true : false,
                'discount_amount' => $discountAmount,
                'discount_name' => $discount?->nama_diskon ?? null
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user', 'items'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => (new TransactionResource($transaction))->resolve()
        ]);
    }
}
