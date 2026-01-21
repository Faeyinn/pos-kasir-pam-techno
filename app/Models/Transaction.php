<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'transaction_number',
        'user_id',
        'discount_id',
        'discount_amount',
        'payment_type',
        'payment_method',
        'subtotal',
        'total',
        'amount_received',
        'change',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'integer',
        'total' => 'integer',
        'amount_received' => 'integer',
        'change' => 'integer',
        'discount_amount' => 'integer'
    ];

    /**
     * Get the user who created this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the discount applied to this transaction
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get the items in this transaction
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
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
        return $this->items->sum('qty');
    }
}

