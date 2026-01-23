<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $table = 'transaksi';

    protected $primaryKey = 'id_transaksi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nomor_transaksi',
        'id_user',
        'jenis_transaksi',
        'metode_pembayaran',
        'total_belanja',
        'diskon',
        'total_transaksi',
        'jumlah_dibayar',
        'kembalian',
        'id_diskon',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_belanja' => 'integer',
        'diskon' => 'integer',
        'total_transaksi' => 'integer',
        'jumlah_dibayar' => 'integer',
        'kembalian' => 'integer'
    ];

    /**
     * Get the user who created this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Get the items in this transaction
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'id_transaksi', 'id_transaksi');
    }

    /**
     * Get the discount applied to this transaction
     */
    public function appliedDiscount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'id_diskon', 'id_diskon');
    }

    /**
     * Generate unique transaction number
     */
    public static function generateTransactionNumber(): string
    {
        return 'TRX-' . date('Ymd') . '-' . str_pad(
            self::whereDate('created_at', today())->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Get total items count
     */
    public function getItemsCount(): int
    {
        return $this->items->sum('jumlah');
    }
}

