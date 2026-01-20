<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'target_type',
        'start_date',
        'end_date',
        'is_active',
        'auto_activate'
    ];

    protected $casts = [
        'value' => 'integer',
        'start_date' => 'datetime',  // Changed from 'date' to 'datetime'
        'end_date' => 'datetime',    // Changed from 'date' to 'datetime'
        'is_active' => 'boolean',
        'auto_activate' => 'boolean'
    ];

    /**
     * Get products associated with this discount
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_product');
    }

    /**
     * Get tags associated with this discount
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'discount_tag');
    }

    /**
     * Get transactions that used this discount
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope untuk filter diskon yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('is_active', true)
              ->orWhere('auto_activate', true);
        })
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now());
    }

    /**
     * Check apakah diskon sedang berlaku
     */
    public function isValid(): bool
    {
        $activeFlags = $this->is_active || $this->auto_activate;
        if (!$activeFlags) {
            return false;
        }

        $now = now();
        return $now >= $this->start_date && $now <= $this->end_date;
    }

    /**
     * Get computed status
     */
    public function getStatusAttribute(): string
    {
        $now = now();
        
        if ($now < $this->start_date) {
            return 'waiting'; // Menunggu
        }
        
        if ($now > $this->end_date) {
            return 'expired'; // Berakhir
        }

        // Within date range
        if ($this->is_active || $this->auto_activate) {
            return 'active'; // Aktif
        }

        return 'disabled'; // Dinonaktifkan (Manual)
    }
}
