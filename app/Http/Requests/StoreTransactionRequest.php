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
            'payment_type' => 'required|in:retail,wholesale',
            'payment_method' => 'required|in:tunai,kartu,qris,ewallet',
            'amount_received' => 'required|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|integer|min:0'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_type.required' => 'Tipe pembayaran wajib dipilih',
            'payment_type.in' => 'Tipe pembayaran tidak valid',
            'payment_method.required' => 'Metode pembayaran wajib dipilih',
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'amount_received.required' => 'Jumlah uang yang diterima wajib diisi',
            'amount_received.integer' => 'Jumlah uang yang diterima harus berupa angka',
            'amount_received.min' => 'Jumlah uang yang diterima tidak boleh negatif',
            'items.required' => 'Minimal satu item harus ditambahkan',
            'items.min' => 'Minimal satu item harus ditambahkan',
            'items.*.product_id.required' => 'Produk wajib dipilih',
            'items.*.product_id.exists' => 'Produk tidak ditemukan',
            'items.*.qty.required' => 'Jumlah item wajib diisi',
            'items.*.qty.integer' => 'Jumlah item harus berupa angka',
            'items.*.qty.min' => 'Jumlah item minimal 1',
            'items.*.price.required' => 'Harga item wajib diisi',
            'items.*.price.integer' => 'Harga item harus berupa angka',
            'items.*.price.min' => 'Harga item tidak boleh negatif',
        ];
    }
}
