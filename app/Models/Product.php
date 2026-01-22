<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'produk';

    protected $primaryKey = 'id_produk';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_produk',
        'gambar',
        'stok',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'stok' => 'integer'
    ];

    /**
     * Get tags associated with this product
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'produk_tag', 'id_produk', 'id_tag');
    }

    /**
     * Get discounts associated with this product
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'diskon_produk', 'id_produk', 'id_diskon');
    }

    /**
     * Get transaction items for this product
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'id_produk', 'id_produk');
    }

    public function satuan(): HasMany
    {
        return $this->hasMany(ProdukSatuan::class, 'id_produk', 'id_produk');
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
        return $this->stok < $threshold;
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMargin(): int
    {
        $defaultSatuan = $this->satuan()->where('is_default', true)->first();
        if (!$defaultSatuan) {
            return 0;
        }

        return (int) ($defaultSatuan->harga_jual - $defaultSatuan->harga_pokok);
    }
}

