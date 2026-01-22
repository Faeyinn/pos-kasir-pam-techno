<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $table = 'tag';

    protected $primaryKey = 'id_tag';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_tag',
        'slug',
        'color'
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['id', 'name'];

    /**
     * Get the 'id' attribute (alias for id_tag)
     */
    public function getIdAttribute(): int
    {
        return $this->id_tag;
    }

    /**
     * Get the 'name' attribute (alias for nama_tag)
     */
    public function getNameAttribute(): ?string
    {
        return $this->nama_tag;
    }

    /**
     * Get products that have this tag
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'produk_tag', 'id_tag', 'id_produk');
    }

    /**
     * Get discounts that apply to this tag
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'diskon_tag', 'id_tag', 'id_diskon');
    }

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->nama_tag);
            }
        });

        static::updating(function (Tag $tag) {
            if ($tag->isDirty('nama_tag') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->nama_tag);
            }
        });
    }
}

