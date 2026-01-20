<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'cost_price' => 'required|integer|min:0',
            'wholesale' => 'nullable|integer|min:0',
            'wholesale_unit' => 'nullable|string|max:50',
            'wholesale_qty_per_unit' => 'nullable|integer|min:1',
            'stock' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
            'tag_ids' => 'required|array|min:1',
            'tag_ids.*' => 'exists:tags,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi',
            'name.max' => 'Nama produk maksimal 255 karakter',
            'price.required' => 'Harga jual wajib diisi',
            'price.integer' => 'Harga jual harus berupa angka',
            'price.min' => 'Harga jual tidak boleh negatif',
            'cost_price.required' => 'Harga modal wajib diisi',
            'cost_price.integer' => 'Harga modal harus berupa angka',
            'cost_price.min' => 'Harga modal tidak boleh negatif',
            'stock.required' => 'Stok wajib diisi',
            'stock.integer' => 'Stok harus berupa angka',
            'stock.min' => 'Stok tidak boleh negatif',
            'tag_ids.required' => 'Minimal satu kategori harus dipilih',
            'tag_ids.min' => 'Minimal satu kategori harus dipilih',
            'tag_ids.*.exists' => 'Kategori tidak valid',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->cost_price > $this->price) {
                $validator->errors()->add('cost_price', 'Harga modal tidak boleh lebih besar dari harga jual');
            }
        });
    }
}
