<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
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
        'change'
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'total' => 'integer',
        'amount_received' => 'integer',
        'change' => 'integer',
        'discount_amount' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public static function generateTransactionNumber()
    {
        return 'TRX-' . date('Ymd') . '-' . str_pad(
            self::whereDate('created_at', today())->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
