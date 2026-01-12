<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category',
        'image',
        'price',
        'wholesale',
        'wholesale_unit',
        'wholesale_qty_per_unit',
        'stock',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'wholesale' => 'integer',
        'stock' => 'integer',
        'wholesale_qty_per_unit' => 'integer'
    ];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
