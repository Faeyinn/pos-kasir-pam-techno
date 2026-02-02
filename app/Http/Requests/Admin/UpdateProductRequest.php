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
            'barcode' => 'nullable|string|max:100',
            'harga_jual' => 'required|integer|min:0',
            'harga_pokok' => 'required|integer|min:0',
            // Grosir multi-satuan (disimpan sebagai baris di tabel produk_satuan)
            'satuan_grosir' => 'nullable|array',
            'satuan_grosir.*.id_satuan' => 'nullable|integer|exists:produk_satuan,id_satuan',
            'satuan_grosir.*.nama_satuan' => 'required|string|max:100',
            'satuan_grosir.*.barcode' => 'nullable|string|max:100',
            'satuan_grosir.*.jumlah_per_satuan' => 'required|integer|min:2',
            'satuan_grosir.*.harga_jual' => 'required|integer|min:0',

            // Legacy single grosir (masih diterima untuk backward compatibility)
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

        $incomingWholesaleUnits = $payload['satuan_grosir'] ?? null;

        // Legacy -> build array satuan_grosir (jika belum dikirim dari UI baru)
        if (!is_array($incomingWholesaleUnits)) {
            $legacyPrice = $payload['harga_jual_grosir'] ?? $payload['wholesale'] ?? null;
            $legacyUnit = $payload['nama_satuan_grosir'] ?? $payload['wholesale_unit'] ?? null;
            $legacyQty = $payload['jumlah_per_satuan_grosir'] ?? $payload['wholesale_qty_per_unit'] ?? null;

            $legacyUnit = is_string($legacyUnit) ? trim($legacyUnit) : '';

            if ($legacyPrice !== null || $legacyUnit !== '' || $legacyQty !== null) {
                $incomingWholesaleUnits = [[
                    'nama_satuan' => $legacyUnit,
                    'jumlah_per_satuan' => $legacyQty,
                    'harga_jual' => $legacyPrice,
                ]];
            } else {
                $incomingWholesaleUnits = [];
            }
        }

        // Sanitasi: buang baris kosong
        $incomingWholesaleUnits = array_values(array_filter((array) $incomingWholesaleUnits, function ($row) {
            if (!is_array($row)) return false;
            $nama = trim((string) ($row['nama_satuan'] ?? ''));
            $qty = (int) ($row['jumlah_per_satuan'] ?? 0);
            $harga = $row['harga_jual'] ?? null;
            return $nama !== '' && $qty > 0 && $harga !== null;
        }));

        $this->merge([
            // Legacy -> Indonesian
            'nama_produk' => $payload['nama_produk'] ?? $payload['name'] ?? null,
            'barcode' => $payload['barcode'] ?? null,
            'harga_jual' => $payload['harga_jual'] ?? $payload['price'] ?? null,
            'harga_pokok' => $payload['harga_pokok'] ?? $payload['cost_price'] ?? null,
            'stok' => $payload['stok'] ?? $payload['stock'] ?? null,
            'harga_jual_grosir' => $payload['harga_jual_grosir'] ?? $payload['wholesale'] ?? null,
            'nama_satuan_grosir' => $payload['nama_satuan_grosir'] ?? $payload['wholesale_unit'] ?? null,
            'jumlah_per_satuan_grosir' => $payload['jumlah_per_satuan_grosir'] ?? $payload['wholesale_qty_per_unit'] ?? null,

            // New
            'satuan_grosir' => $incomingWholesaleUnits,
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

            // Validasi unik nama_satuan pada satu produk (untuk menghindari duplikasi)
            $units = (array) ($this->input('satuan_grosir') ?? []);
            $names = array_map(fn ($u) => mb_strtolower(trim((string) ($u['nama_satuan'] ?? ''))), $units);
            $names = array_values(array_filter($names, fn ($n) => $n !== ''));
            if (count($names) !== count(array_unique($names))) {
                $validator->errors()->add('satuan_grosir', 'Nama satuan grosir tidak boleh duplikat');
            }
        });
    }
}
