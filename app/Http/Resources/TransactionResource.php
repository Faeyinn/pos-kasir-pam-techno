<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_number' => $this->transaction_number,
            'user' => new UserResource($this->whenLoaded('user')),
            'discount' => new DiscountResource($this->whenLoaded('discount')),
            'discount_amount' => $this->discount_amount,
            'payment_type' => $this->payment_type,
            'payment_method' => $this->payment_method,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'amount_received' => $this->amount_received,
            'change' => $this->change,
            'items' => TransactionItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
