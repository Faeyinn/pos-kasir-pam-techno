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
        $tags = Tag::orderBy('nama_tag')->get();
        return response()->json([
            'success' => true,
            'data' => $tags
        ]);
    }

    public function index(Request $request)
    {
        $query = Product::active()->with(['tags', 'satuan']);

        // Filter by Tag (formerly Category)
        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('nama_tag', $request->category);
            });
        }

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('nama_produk')->get();

        // Add discount information to each product
        $products->transform(function ($product) {
            $discount = $this->discountService->getDiscountForProduct($product->id_produk);

            $defaultSatuan = $product->satuan
                ->where('is_active', true)
                ->firstWhere('is_default', true);

            $hargaDefault = $defaultSatuan ? (int) $defaultSatuan->harga_jual : 0;
            
            if ($discount) {
                $discountAmount = 0;
                if ($discount->tipe_diskon === 'persen') {
                    $discountAmount = ($hargaDefault * $discount->nilai_diskon) / 100;
                } else {
                    $discountAmount = min($discount->nilai_diskon, $hargaDefault);
                }

                $discountedPrice = $hargaDefault - $discountAmount;

                $product->discount = [
                    'id_diskon' => $discount->id_diskon,
                    'nama_diskon' => $discount->nama_diskon,
                    'tipe_diskon' => $discount->tipe_diskon,
                    'nilai_diskon' => $discount->nilai_diskon,
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
        $product = Product::with(['tags', 'satuan'])->findOrFail($id);

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
        $product->stok -= $request->qty;
        
        if ($product->stok < 0) {
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
