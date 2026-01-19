<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\Discount;

Route::get('/debug/discount-test', function() {
    // Get first active product
    $product = Product::with(['tags', 'discounts' => function($query) {
        $query->where('is_active', true)
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
    }])->first();
    
    // Get all active discounts
    $discounts = Discount::where('is_active', true)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->with(['products', 'tags'])
        ->get();
    
    return [
        'product' => $product,
        'product_has_discounts' => $product ? $product->discounts->count() : 0,
        'all_active_discounts' => $discounts->count(),
        'discount_details' => $discounts->map(function($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $d->type,
                'value' => $d->value,
                'is_active' => $d->is_active,
                'products_count' => $d->products->count(),
                'tags_count' => $d->tags->count(),
            ];
        })
    ];
});
