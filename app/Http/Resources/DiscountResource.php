<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_diskon' => $this->id_diskon,
            'nama_diskon' => $this->nama_diskon,
            'tipe_diskon' => $this->tipe_diskon,
            'nilai_diskon' => $this->nilai_diskon,
            'target' => $this->target,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'is_active' => $this->is_active,
            'auto_active' => $this->auto_active,
            'is_valid' => $this->isValid(),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
