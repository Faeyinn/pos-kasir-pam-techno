<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            // Indonesian-first keys (legacy keys are normalized in prepareForValidation)
            'nama_produk' => 'required|string|max:255',
            'harga_jual' => 'required|integer|min:0',
            'harga_pokok' => 'required|integer|min:0',
            'harga_jual_grosir' => 'nullable|integer|min:0',
            'nama_satuan_grosir' => 'nullable|string|max:50',
            'jumlah_per_satuan_grosir' => 'nullable|integer|min:1',
            'stok' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'tag_ids' => 'required|array|min:1',
            'tag_ids.*' => 'exists:tag,id_tag',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nama_produk.required' => 'Nama produk wajib diisi',
            'nama_produk.max' => 'Nama produk maksimal 255 karakter',
            'harga_jual.required' => 'Harga jual wajib diisi',
            'harga_jual.integer' => 'Harga jual harus berupa angka',
            'harga_jual.min' => 'Harga jual tidak boleh negatif',
            'harga_pokok.required' => 'Harga modal wajib diisi',
            'harga_pokok.integer' => 'Harga modal harus berupa angka',
            'harga_pokok.min' => 'Harga modal tidak boleh negatif',
            'stok.required' => 'Stok wajib diisi',
            'stok.integer' => 'Stok harus berupa angka',
            'stok.min' => 'Stok tidak boleh negatif',
            'tag_ids.required' => 'Minimal satu kategori harus dipilih',
            'tag_ids.min' => 'Minimal satu kategori harus dipilih',
            'tag_ids.*.exists' => 'Kategori tidak valid',
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->all();

        $this->merge([
            // Legacy -> Indonesian
            'nama_produk' => $payload['nama_produk'] ?? $payload['name'] ?? null,
            'harga_jual' => $payload['harga_jual'] ?? $payload['price'] ?? null,
            'harga_pokok' => $payload['harga_pokok'] ?? $payload['cost_price'] ?? null,
            'stok' => $payload['stok'] ?? $payload['stock'] ?? null,
            'harga_jual_grosir' => $payload['harga_jual_grosir'] ?? $payload['wholesale'] ?? null,
            'nama_satuan_grosir' => $payload['nama_satuan_grosir'] ?? $payload['wholesale_unit'] ?? null,
            'jumlah_per_satuan_grosir' => $payload['jumlah_per_satuan_grosir'] ?? $payload['wholesale_qty_per_unit'] ?? null,
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->harga_pokok > $this->harga_jual) {
                $validator->errors()->add('harga_pokok', 'Harga modal tidak boleh lebih besar dari harga jual');
            }
        });
    }
}
