<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_tag' => $this->id_tag,
            'nama_tag' => $this->nama_tag,
            'slug' => $this->slug,
            'color' => $this->color,
        ];
    }
}
