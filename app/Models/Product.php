<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'image',
        'price',
        'cost_price',
        'wholesale',
        'wholesale_unit',
        'wholesale_qty_per_unit',
        'stock',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'cost_price' => 'integer',
        'wholesale' => 'integer',
        'stock' => 'integer',
        'wholesale_qty_per_unit' => 'integer'
    ];

    /**
     * Get tags associated with this product
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get discounts associated with this product
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_product');
    }

    /**
     * Get transaction items for this product
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Scope for active products
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if product has low stock
     */
    public function hasLowStock(int $threshold = 20): bool
    {
        return $this->stock < $threshold;
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMargin(): int
    {
        return $this->price - $this->cost_price;
    }
}

