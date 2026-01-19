<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Tag;
use App\Services\DiscountService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Get all tags for filtering
     */
    public function getTags()
    {
        $tags = Tag::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $tags
        ]);
    }

    public function index(Request $request)
    {
        $query = Product::active()->with('tags');

        // Filter by Tag (formerly Category)
        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->get();
        
        // Hide cost_price from kasir
        $products->makeHidden(['cost_price']);

        // Add discount information to each product
        $products->transform(function ($product) {
            $discount = $this->discountService->getDiscountForProduct($product->id);
            
            if ($discount) {
                // Calculate discounted price
                $discountAmount = 0;
                if ($discount->type === 'percentage') {
                    $discountAmount = ($product->price * $discount->value) / 100;
                } else {
                    $discountAmount = min($discount->value, $product->price);
                }
                
                $discountedPrice = $product->price - $discountAmount;
                
                $product->discount = [
                    'id' => $discount->id,
                    'name' => $discount->name,
                    'type' => $discount->type,
                    'value' => $discount->value,
                    'discounted_price' => (int) $discountedPrice,
                    'discount_amount' => (int) $discountAmount,
                ];
            } else {
                $product->discount = null;
            }
            
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function show($id)
    {
        $product = Product::with('tags')->findOrFail($id);
        
        // Hide cost_price from kasir
        $product->makeHidden(['cost_price']);

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'qty' => 'required|integer'
        ]);

        $product = Product::findOrFail($id);
        $product->stock -= $request->qty;
        
        if ($product->stock < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi'
            ], 400);
        }

        $product->save();

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }
}
