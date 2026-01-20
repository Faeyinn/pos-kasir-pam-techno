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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'price' => $this->price,
            'cost_price' => $this->when($this->shouldShowCostPrice($request), $this->cost_price),
            'wholesale' => $this->wholesale,
            'wholesale_unit' => $this->wholesale_unit,
            'wholesale_qty_per_unit' => $this->wholesale_qty_per_unit,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'tag_ids' => $this->when($this->relationLoaded('tags'), fn() => $this->tags->pluck('id')->toArray()),
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
