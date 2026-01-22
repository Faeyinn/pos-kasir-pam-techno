<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * DiscountService
 * 
 * Handles all discount-related business logic.
 * Responsibilities:
 * - Check active discounts
 * - Match products/tags with discounts
 * - Calculate discount amounts
 * - Return applicable discount for a transaction
 */
class DiscountService
{
    /**
     * Find applicable discount for given cart items
     * 
     * @param  Collection  $cartItems  Collection of items with product_id and qty
     * @return array ['discount' => Discount|null, 'amount' => int]
     */
    public function findApplicableDiscount(Collection $cartItems): array
    {
        // Get all active discounts
        $activeDiscounts = Discount::active()
            ->with(['products', 'tags'])
            ->get();

        if ($activeDiscounts->isEmpty()) {
            return ['discount' => null, 'amount' => 0];
        }

        // Check each discount for applicability
        foreach ($activeDiscounts as $discount) {
            $discountAmount = $this->calculateDiscountForCart($discount, $cartItems);
            
            if ($discountAmount > 0) {
                // Return first matching discount (MVP: no combination)
                return [
                    'discount' => $discount,
                    'amount' => $discountAmount
                ];
            }
        }

        return ['discount' => null, 'amount' => 0];
    }

    /**
     * Calculate discount amount for entire cart
     * 
     * @param  Discount  $discount
     * @param  Collection  $cartItems
     * @return int
     */
    private function calculateDiscountForCart(Discount $discount, Collection $cartItems): int
    {
        $eligibleTotal = 0;

        foreach ($cartItems as $item) {
            if ($this->isProductEligible($discount, $item['product_id'])) {
                $eligibleTotal += ($item['price'] * $item['qty']);
            }
        }

        if ($eligibleTotal == 0) {
            return 0;
        }

        return $this->calculateDiscountValue($discount, $eligibleTotal);
    }

    /**
     * Check if product is eligible for discount
     * 
     * @param  Discount  $discount
     * @param  int  $productId
     * @return bool
     */
    private function isProductEligible(Discount $discount, int $productId): bool
    {
        if ($discount->target === 'produk') {
            // Check direct product association
            return $discount->products->contains(fn ($p) => (int) $p->id_produk === (int) $productId);
        }

        if ($discount->target === 'tag') {
            // Check if product has any of the discount's tags
            $product = Product::with('tags')->find($productId);
            
            if (!$product) {
                return false;
            }

            $discountTagIds = $discount->tags->pluck('id_tag')->toArray();
            $productTagIds = $product->tags->pluck('id_tag')->toArray();

            return count(array_intersect($discountTagIds, $productTagIds)) > 0;
        }

        return false;
    }

    /**
     * Calculate actual discount value based on type
     * 
     * @param  Discount  $discount
     * @param  int  $subtotal
     * @return int
     */
    private function calculateDiscountValue(Discount $discount, int $subtotal): int
    {
        if ($discount->tipe_diskon === 'persen') {
            // Percentage discount
            $amount = ($subtotal * $discount->nilai_diskon) / 100;
            return (int) floor($amount);
        }

        if ($discount->tipe_diskon === 'nominal') {
            // Fixed amount discount (max discount = subtotal)
            return min($discount->nilai_diskon, $subtotal);
        }

        return 0;
    }

    /**
     * Get discount for single product (for display purposes)
     * 
     * @param  int  $productId
     * @return Discount|null
     */
    public function getDiscountForProduct(int $productId): ?Discount
    {
        $discount = Discount::active()
            ->where(function ($query) use ($productId) {
                // Product-based discount
                $query->where('target', 'produk')
                    ->whereHas('products', function ($q) use ($productId) {
                        $q->where('produk.id_produk', $productId);
                    });
            })
            ->orWhere(function ($query) use ($productId) {
                // Tag-based discount
                $query->where('target', 'tag')
                    ->whereHas('tags', function ($q) use ($productId) {
                        $q->whereIn('tag.id_tag', function ($subQuery) use ($productId) {
                            $subQuery->select('id_tag')
                                ->from('produk_tag')
                                ->where('id_produk', $productId);
                        });
                    });
            })
            ->first();

        return $discount;
    }

    /**
     * Log discount application (for debugging/audit)
     * 
     * @param  Discount  $discount
     * @param  int  $amount
     * @param  string  $transactionNumber
     */
    public function logDiscountApplication(Discount $discount, int $amount, string $transactionNumber): void
    {
        Log::info('Discount Applied', [
            'discount_id' => $discount->id_diskon,
            'discount_name' => $discount->nama_diskon,
            'amount' => $amount,
            'transaction' => $transactionNumber
        ]);
    }
}
