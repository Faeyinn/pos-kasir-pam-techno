<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $table = 'detail_transaksi';

    protected $primaryKey = 'id_detail_transaksi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_transaksi',
        'id_produk',
        'id_satuan',
        'nama_produk',
        'nama_satuan',
        'jumlah_per_satuan',
        'jumlah',
        'harga_pokok',
        'harga_jual',
        'subtotal',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'jumlah' => 'integer',
        'jumlah_per_satuan' => 'integer',
        'harga_pokok' => 'integer',
        'harga_jual' => 'integer',
        'subtotal' => 'integer'
    ];

    /**
     * Get the transaction this item belongs to
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'id_transaksi', 'id_transaksi');
    }

    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_produk', 'id_produk');
    }
}

