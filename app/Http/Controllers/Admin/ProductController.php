<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of products for admin
     */
    public function index()
    {
        $products = Product::with(['tags', 'discounts' => function($query) {
            $query->where('is_active', true)
                  ->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
        }])->orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        
        return view('pages.admin.products', compact('products', 'tags'));
    }

    /**
     * Get single product data for editing (JSON)
     */
    public function show($id)
    {
        $product = Product::with('tags')->findOrFail($id);
        
        // Add tag_ids array for form binding
        $product->tag_ids = $product->tags->pluck('id')->toArray();
        
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Store new product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'cost_price' => 'required|integer|min:0',
            'wholesale' => 'nullable|integer|min:0',
            'wholesale_unit' => 'nullable|string|max:50',
            'wholesale_qty_per_unit' => 'nullable|integer|min:1',
            'stock' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'tag_ids' => 'required|array|min:1',
            'tag_ids.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate profit margin (cost_price should not exceed price)
        if ($request->cost_price > $request->price) {
            return response()->json([
                'success' => false,
                'message' => 'Harga modal tidak boleh lebih besar dari harga jual',
                'errors' => [
                    'cost_price' => ['Harga modal tidak boleh lebih besar dari harga jual']
                ]
            ], 422);
        }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'cost_price' => $request->cost_price,
            'wholesale' => $request->wholesale ?? 0,
            'wholesale_unit' => $request->wholesale_unit,
            'wholesale_qty_per_unit' => $request->wholesale_qty_per_unit ?? 1,
            'stock' => $request->stock,
            'is_active' => $request->is_active
        ]);

        // Sync tags
        $product->tags()->sync($request->tag_ids);
        
        // Load tags for response
        $product->load('tags');
        $product->tag_ids = $product->tags->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }

    /**
     * Update product data
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'cost_price' => 'required|integer|min:0',
            'wholesale' => 'nullable|integer|min:0',
            'wholesale_unit' => 'nullable|string|max:50',
            'wholesale_qty_per_unit' => 'nullable|integer|min:1',
            'stock' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'tag_ids' => 'required|array|min:1',
            'tag_ids.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate profit margin (cost_price should not exceed price)
        if ($request->cost_price > $request->price) {
            return response()->json([
                'success' => false,
                'message' => 'Harga modal tidak boleh lebih besar dari harga jual',
                'errors' => [
                    'cost_price' => ['Harga modal tidak boleh lebih besar dari harga jual']
                ]
            ], 422);
        }

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'cost_price' => $request->cost_price,
            'wholesale' => $request->wholesale ?? 0,
            'wholesale_unit' => $request->wholesale_unit,
            'wholesale_qty_per_unit' => $request->wholesale_qty_per_unit ?? 1,
            'stock' => $request->stock,
            'is_active' => $request->is_active
        ]);

        // Sync tags
        $product->tags()->sync($request->tag_ids);
        
        // Load tags for response
        $product->load('tags');
        $product->tag_ids = $product->tags->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $product
        ]);
    }

    /**
     * Delete product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Check if product has transactions
        if ($product->transactionItems()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
