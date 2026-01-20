<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:0',
            'target_type' => 'required|in:product,tag',
            'target_ids' => 'required|array|min:1',
            'target_ids.*' => 'required|integer',
            'start_date' => 'required',
            'end_date' => 'required',
            'is_active' => 'boolean',
            'auto_activate' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama diskon wajib diisi',
            'name.max' => 'Nama diskon maksimal 255 karakter',
            'type.required' => 'Tipe diskon wajib dipilih',
            'type.in' => 'Tipe diskon tidak valid',
            'value.required' => 'Nilai diskon wajib diisi',
            'value.integer' => 'Nilai diskon harus berupa angka',
            'value.min' => 'Nilai diskon tidak boleh negatif',
            'target_type.required' => 'Target diskon wajib dipilih',
            'target_type.in' => 'Target diskon tidak valid',
            'target_ids.required' => 'Minimal satu target harus dipilih',
            'target_ids.min' => 'Minimal satu target harus dipilih',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'end_date.required' => 'Tanggal selesai wajib diisi',
        ];
    }
}
