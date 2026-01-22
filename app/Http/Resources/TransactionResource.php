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
            'id_transaksi' => $this->id_transaksi,
            'nomor_transaksi' => $this->nomor_transaksi,
            'user' => new UserResource($this->whenLoaded('user')),
            'jenis_transaksi' => $this->jenis_transaksi,
            'metode_pembayaran' => $this->metode_pembayaran,
            'total_belanja' => $this->total_belanja,
            'diskon' => $this->diskon,
            'total_transaksi' => $this->total_transaksi,
            'jumlah_dibayar' => $this->jumlah_dibayar,
            'kembalian' => $this->kembalian,
            'items' => TransactionItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
