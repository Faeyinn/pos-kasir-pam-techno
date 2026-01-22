<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    protected $table = 'diskon';

    protected $primaryKey = 'id_diskon';

    protected $fillable = [
        'nama_diskon',
        'tipe_diskon',
        'nilai_diskon',
        'target',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'auto_active'
    ];

    protected $casts = [
        'nilai_diskon' => 'integer',
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'is_active' => 'boolean',
        'auto_active' => 'boolean'
    ];

    /**
     * Get products associated with this discount
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'diskon_produk', 'id_diskon', 'id_produk');
    }

    /**
     * Get tags associated with this discount
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'diskon_tag', 'id_diskon', 'id_tag');
    }

    /**
     * Scope untuk filter diskon yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
                        $q->where('is_active', true)
                            ->orWhere('auto_active', true);
        })
                ->where('tanggal_mulai', '<=', now())
                ->where('tanggal_selesai', '>=', now());
    }

    /**
     * Check apakah diskon sedang berlaku
     */
    public function isValid(): bool
    {
        $activeFlags = $this->is_active || $this->auto_active;
        if (!$activeFlags) {
            return false;
        }

        $now = now();
        return $now >= $this->tanggal_mulai && $now <= $this->tanggal_selesai;
    }

    /**
     * Get computed status
     */
    public function getStatusAttribute(): string
    {
        $now = now();
        
        if ($now < $this->tanggal_mulai) {
            return 'waiting'; // Menunggu
        }
        
        if ($now > $this->tanggal_selesai) {
            return 'expired'; // Berakhir
        }

        // Within date range
        if ($this->is_active || $this->auto_active) {
            return 'active'; // Aktif
        }

        return 'disabled'; // Dinonaktifkan (Manual)
    }
}
