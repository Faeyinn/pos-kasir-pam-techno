<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc');

        // Filter by user (kasir)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $transactions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:retail,wholesale',
            'payment_method' => 'required|in:tunai,kartu,qris,ewallet',
            'amount_received' => 'required|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        
        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['qty'] * $item['price'];
            }

            $total = $subtotal;
            $change = $request->amount_received - $total;

            if ($change < 0) {
                throw new \Exception('Jumlah uang yang diterima kurang dari total');
            }

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => Transaction::generateTransactionNumber(),
                'user_id' => auth()->id() ?? 1, // Default to user ID 1 if not authenticated
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'subtotal' => $subtotal,
                'total' => $total,
                'amount_received' => $request->amount_received,
                'change' => $change
            ]);

            // Create transaction items and update stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Calculate actual quantity to deduct from stock
                $isWholesale = $request->payment_type === 'wholesale' && $product->wholesale > 0;
                $qtyToDeduct = $isWholesale ? ($item['qty'] * $product->wholesale_qty_per_unit) : $item['qty'];

                // Check stock
                if ($product->stock < $qtyToDeduct) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi");
                }

                // Create transaction item
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price']
                ]);

                // Update stock
                $product->decrement('stock', $qtyToDeduct);
            }

            DB::commit();

            // Load relationships
            $transaction->load(['user', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'data' => $transaction
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
        $transaction = Transaction::with(['user', 'items.product'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }
}
