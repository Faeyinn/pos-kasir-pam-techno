<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'jenis_transaksi' => 'nullable|in:eceran,grosir',
            'metode_pembayaran' => 'required|in:tunai,kartu,qris,ewallet',
            'jumlah_dibayar' => 'required|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.id_produk' => 'required|exists:produk,id_produk',
            'items.*.id_satuan' => 'required|exists:produk_satuan,id_satuan',
            'items.*.jumlah' => 'required|integer|min:1'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'jenis_transaksi.in' => 'Jenis transaksi tidak valid',
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih',
            'metode_pembayaran.in' => 'Metode pembayaran tidak valid',
            'jumlah_dibayar.required' => 'Jumlah uang yang dibayar wajib diisi',
            'jumlah_dibayar.integer' => 'Jumlah uang yang dibayar harus berupa angka',
            'jumlah_dibayar.min' => 'Jumlah uang yang dibayar tidak boleh negatif',
            'items.required' => 'Minimal satu item harus ditambahkan',
            'items.min' => 'Minimal satu item harus ditambahkan',
            'items.*.id_produk.required' => 'Produk wajib dipilih',
            'items.*.id_produk.exists' => 'Produk tidak ditemukan',
            'items.*.id_satuan.required' => 'Satuan produk wajib dipilih',
            'items.*.id_satuan.exists' => 'Satuan produk tidak ditemukan',
            'items.*.jumlah.required' => 'Jumlah item wajib diisi',
            'items.*.jumlah.integer' => 'Jumlah item harus berupa angka',
            'items.*.jumlah.min' => 'Jumlah item minimal 1',
        ];
    }
}
