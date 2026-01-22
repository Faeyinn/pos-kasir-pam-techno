<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdukSatuan extends Model
{
    protected $table = 'produk_satuan';

    protected $primaryKey = 'id_satuan';

    protected $fillable = [
        'id_produk',
        'nama_satuan',
        'jumlah_per_satuan',
        'harga_pokok',
        'harga_jual',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'jumlah_per_satuan' => 'integer',
        'harga_pokok' => 'integer',
        'harga_jual' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_produk', 'id_produk');
    }
}
