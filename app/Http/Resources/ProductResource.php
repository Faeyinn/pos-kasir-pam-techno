<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $showCost = $this->shouldShowCostPrice($request);

        return [
            'id_produk' => $this->id_produk,
            'nama_produk' => $this->nama_produk,
            'gambar' => $this->gambar,
            'stok' => $this->stok,
            'is_active' => $this->is_active,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'tag_ids' => $this->when($this->relationLoaded('tags'), fn() => $this->tags->pluck('id_tag')->toArray()),
            'satuan' => $this->whenLoaded('satuan', function () use ($showCost) {
                return $this->satuan
                    ->where('is_active', true)
                    ->sortByDesc('is_default')
                    ->values()
                    ->map(function ($satuan) use ($showCost) {
                        return [
                            'id_satuan' => $satuan->id_satuan,
                            'nama_satuan' => $satuan->nama_satuan,
                            'jumlah_per_satuan' => $satuan->jumlah_per_satuan,
                            'harga_jual' => (int) $satuan->harga_jual,
                            'harga_pokok' => $showCost ? (int) $satuan->harga_pokok : null,
                            'is_default' => (bool) $satuan->is_default,
                            'is_active' => (bool) $satuan->is_active,
                        ];
                    });
            }),
            'discount' => $this->when(isset($this->discount), $this->discount),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Determine if cost_price should be shown based on user role
     */
    private function shouldShowCostPrice(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }

        // Only show cost_price to admin or master users
        return in_array($user->role, ['admin', 'master']);
    }
}
