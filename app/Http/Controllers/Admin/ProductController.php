<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProdukSatuan;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    private function formatProductForAdmin(Product $product): array
    {
        $product->loadMissing(['tags', 'satuan', 'discounts']);

        $defaultSatuan = $product->satuan
            ->where('is_active', true)
            ->firstWhere('is_default', true)
            ?? $product->satuan->where('is_active', true)->first();

        $wholesaleSatuan = $product->satuan
            ->where('is_active', true)
            ->where('is_default', false)
            ->sortByDesc('jumlah_per_satuan')
            ->first();

        $hargaJual = (int) ($defaultSatuan->harga_jual ?? 0);
        $hargaPokok = (int) ($defaultSatuan->harga_pokok ?? 0);
        $hargaJualGrosir = (int) ($wholesaleSatuan->harga_jual ?? 0);
        $namaSatuanGrosir = $wholesaleSatuan->nama_satuan ?? null;
        $jumlahPerSatuanGrosir = (int) ($wholesaleSatuan->jumlah_per_satuan ?? 1);

        return [
            // Indonesian keys (schema-aligned)
            'id_produk' => $product->id_produk,
            'nama_produk' => $product->nama_produk,
            'harga_jual' => $hargaJual,
            'harga_pokok' => $hargaPokok,
            'harga_jual_grosir' => $hargaJualGrosir,
            'nama_satuan_grosir' => $namaSatuanGrosir,
            'jumlah_per_satuan_grosir' => $jumlahPerSatuanGrosir,
            'stok' => (int) $product->stok,

            // Legacy keys (admin UI compatibility)
            'id' => $product->id_produk,
            'name' => $product->nama_produk,
            'price' => $hargaJual,
            'cost_price' => $hargaPokok,
            'wholesale' => $hargaJualGrosir,
            'wholesale_unit' => $namaSatuanGrosir,
            'wholesale_qty_per_unit' => $jumlahPerSatuanGrosir,
            'stock' => (int) $product->stok,
            'is_active' => (bool) $product->is_active,
            'tags' => $product->tags
                ->map(fn ($t) => [
                    'id' => $t->id_tag,
                    'name' => $t->nama_tag,
                    'slug' => $t->slug,
                    'color' => $t->color,
                ])
                ->values(),
            'tag_ids' => $product->tags->pluck('id_tag')->values()->toArray(),
            'discounts' => $product->discounts
                ->map(fn ($d) => [
                    'id' => $d->id_diskon,
                    'name' => $d->nama_diskon,
                    'type' => $d->tipe_diskon === 'persen' ? 'percentage' : 'fixed',
                    'value' => (int) $d->nilai_diskon,
                ])
                ->values(),
        ];
    }

    /**
     * Display a listing of products for admin
     */
    public function index(): View|JsonResponse
    {
        $products = Product::with([
            'tags',
            'satuan',
            'discounts' => fn ($q) => $q->active(),
        ])
            ->orderBy('nama_produk')
            ->get()
            ->map(fn (Product $p) => $this->formatProductForAdmin($p))
            ->values();

        $tags = Tag::orderBy('nama_tag')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id_tag,
                'name' => $t->nama_tag,
                'slug' => $t->slug,
                'color' => $t->color,
            ])
            ->values();

        if (request()->is('api/*') || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $products,
                'tags' => $tags,
            ]);
        }

        return view('pages.admin.products', compact('products', 'tags'));
    }

    /**
     * Get single product data for editing (JSON)
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with(['tags', 'satuan', 'discounts' => fn ($q) => $q->active()])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatProductForAdmin($product)
        ]);
    }

    /**
     * Store new product
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $product = Product::create([
                'nama_produk' => $validated['nama_produk'],
                'stok' => (int) $validated['stok'],
                'is_active' => (bool) $validated['is_active'],
            ]);

            // Default unit (Pcs)
            ProdukSatuan::create([
                'id_produk' => $product->id_produk,
                'nama_satuan' => 'Pcs',
                'jumlah_per_satuan' => 1,
                'harga_pokok' => (int) $validated['harga_pokok'],
                'harga_jual' => (int) $validated['harga_jual'],
                'is_default' => true,
                'is_active' => true,
            ]);

            // Optional wholesale unit (single unit supported by current admin UI)
            $wholesalePrice = (int) ($validated['harga_jual_grosir'] ?? 0);
            $wholesaleUnit = trim((string) ($validated['nama_satuan_grosir'] ?? ''));
            $wholesaleQtyPerUnit = (int) ($validated['jumlah_per_satuan_grosir'] ?? 1);

            if ($wholesalePrice > 0 && $wholesaleQtyPerUnit > 1 && $wholesaleUnit !== '') {
                ProdukSatuan::create([
                    'id_produk' => $product->id_produk,
                    'nama_satuan' => $wholesaleUnit,
                    'jumlah_per_satuan' => $wholesaleQtyPerUnit,
                    // Assume cost_price is per Pcs
                    'harga_pokok' => (int) $validated['harga_pokok'] * $wholesaleQtyPerUnit,
                    'harga_jual' => $wholesalePrice,
                    'is_default' => false,
                    'is_active' => true,
                ]);
            }

            // Sync tags
            $product->tags()->sync($validated['tag_ids']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $this->formatProductForAdmin($product->fresh(['tags', 'satuan', 'discounts' => fn ($q) => $q->active()]))
        ], 201);
    }

    /**
     * Update product data
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::with('satuan')->findOrFail($id);
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $product->update([
                'nama_produk' => $validated['nama_produk'],
                'stok' => (int) $validated['stok'],
                'is_active' => (bool) $validated['is_active'],
            ]);

            $defaultSatuan = $product->satuan->firstWhere('is_default', true);
            if (!$defaultSatuan) {
                $defaultSatuan = ProdukSatuan::create([
                    'id_produk' => $product->id_produk,
                    'nama_satuan' => 'Pcs',
                    'jumlah_per_satuan' => 1,
                    'harga_pokok' => 0,
                    'harga_jual' => 0,
                    'is_default' => true,
                    'is_active' => true,
                ]);
            }

            $defaultSatuan->update([
                'nama_satuan' => $defaultSatuan->nama_satuan ?: 'Pcs',
                'jumlah_per_satuan' => 1,
                'harga_pokok' => (int) $validated['harga_pokok'],
                'harga_jual' => (int) $validated['harga_jual'],
                'is_default' => true,
                'is_active' => true,
            ]);

            $wholesalePrice = (int) ($validated['harga_jual_grosir'] ?? 0);
            $wholesaleUnit = trim((string) ($validated['nama_satuan_grosir'] ?? ''));
            $wholesaleQtyPerUnit = (int) ($validated['jumlah_per_satuan_grosir'] ?? 1);

            if ($wholesalePrice > 0 && $wholesaleQtyPerUnit > 1 && $wholesaleUnit !== '') {
                $wholesaleSatuan = $product->satuan
                    ->where('is_default', false)
                    ->firstWhere('nama_satuan', $wholesaleUnit);

                if (!$wholesaleSatuan) {
                    $wholesaleSatuan = ProdukSatuan::create([
                        'id_produk' => $product->id_produk,
                        'nama_satuan' => $wholesaleUnit,
                        'jumlah_per_satuan' => $wholesaleQtyPerUnit,
                        'harga_pokok' => (int) $validated['harga_pokok'] * $wholesaleQtyPerUnit,
                        'harga_jual' => $wholesalePrice,
                        'is_default' => false,
                        'is_active' => true,
                    ]);
                } else {
                    $wholesaleSatuan->update([
                        'jumlah_per_satuan' => $wholesaleQtyPerUnit,
                        'harga_pokok' => (int) $validated['harga_pokok'] * $wholesaleQtyPerUnit,
                        'harga_jual' => $wholesalePrice,
                        'is_active' => true,
                    ]);
                }
            } else {
                // If wholesale is disabled in the form, deactivate existing non-default units created via this UI
                $product->satuan
                    ->where('is_default', false)
                    ->each(fn ($s) => $s->update(['is_active' => false]));
            }

            // Sync tags
            $product->tags()->sync($validated['tag_ids']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $this->formatProductForAdmin($product->fresh(['tags', 'satuan', 'discounts' => fn ($q) => $q->active()]))
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        
        // Check if product has transactions
        if ($product->transactionItems()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
