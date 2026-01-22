<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_detail_transaksi' => $this->id_detail_transaksi,
            'id_produk' => $this->id_produk,
            'id_satuan' => $this->id_satuan,
            'nama_produk' => $this->nama_produk,
            'nama_satuan' => $this->nama_satuan,
            'jumlah_per_satuan' => $this->jumlah_per_satuan,
            'jumlah' => $this->jumlah,
            'harga_jual' => $this->harga_jual,
            'subtotal' => $this->subtotal,
        ];
    }
}
