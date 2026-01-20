<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products for admin
     */
    public function index(): View
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
    public function show(int $id): JsonResponse
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
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $product = Product::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'],
            'wholesale' => $validated['wholesale'] ?? 0,
            'wholesale_unit' => $validated['wholesale_unit'] ?? null,
            'wholesale_qty_per_unit' => $validated['wholesale_qty_per_unit'] ?? 1,
            'stock' => $validated['stock'],
            'is_active' => $validated['is_active']
        ]);

        // Sync tags
        $product->tags()->sync($validated['tag_ids']);
        
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
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $validated = $request->validated();

        $product->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'],
            'wholesale' => $validated['wholesale'] ?? 0,
            'wholesale_unit' => $validated['wholesale_unit'] ?? null,
            'wholesale_qty_per_unit' => $validated['wholesale_qty_per_unit'] ?? 1,
            'stock' => $validated['stock'],
            'is_active' => $validated['is_active']
        ]);

        // Sync tags
        $product->tags()->sync($validated['tag_ids']);
        
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
    public function destroy(int $id): JsonResponse
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
