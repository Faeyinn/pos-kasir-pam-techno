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
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductController extends Controller
{
    private function getActiveWholesaleUnits(Product $product): Collection
    {
        $product->loadMissing('satuan');

        return $product->satuan
            ->where('is_active', true)
            ->where('is_default', false)
            ->sortBy('jumlah_per_satuan')
            ->values();
    }

    private function syncWholesaleUnits(Product $product, int $hargaPokokDasar, array $satuanGrosir): void
    {
        $product->loadMissing('satuan');

        // Map existing non-default units by id_satuan
        $existingById = $product->satuan
            ->where('is_default', false)
            ->keyBy('id_satuan');

        $keepIds = [];

        foreach ($satuanGrosir as $row) {
            if (!is_array($row)) {
                continue;
            }

            $idSatuan = isset($row['id_satuan']) ? (int) $row['id_satuan'] : null;
            $namaSatuan = trim((string) ($row['nama_satuan'] ?? ''));
            $barcode = isset($row['barcode']) ? trim((string) $row['barcode']) : null;
            $jumlahPerSatuan = (int) ($row['jumlah_per_satuan'] ?? 0);
            $hargaJual = (int) ($row['harga_jual'] ?? 0);

            // Grosir wajib > 1 (kalau 1 berarti sama dengan satuan dasar)
            if ($namaSatuan === '' || $jumlahPerSatuan < 2) {
                continue;
            }

            $target = null;

            if ($idSatuan && $existingById->has($idSatuan)) {
                $target = $existingById->get($idSatuan);
            }

            // Fallback: cari berdasarkan nama satuan (non-default)
            if (!$target) {
                $target = $product->satuan
                    ->where('is_default', false)
                    ->firstWhere('nama_satuan', $namaSatuan);
            }

            if (!$target) {
                $target = ProdukSatuan::create([
                    'id_produk' => $product->id_produk,
                    'nama_satuan' => $namaSatuan,
                    'barcode' => $barcode,
                    'jumlah_per_satuan' => $jumlahPerSatuan,
                    // Asumsi harga_pokok default adalah per satuan dasar
                    'harga_pokok' => $hargaPokokDasar * $jumlahPerSatuan,
                    'harga_jual' => $hargaJual,
                    'is_default' => false,
                    'is_active' => true,
                ]);
            } else {
                $target->update([
                    'nama_satuan' => $namaSatuan,
                    'barcode' => $barcode,
                    'jumlah_per_satuan' => $jumlahPerSatuan,
                    'harga_pokok' => $hargaPokokDasar * $jumlahPerSatuan,
                    'harga_jual' => $hargaJual,
                    'is_default' => false,
                    'is_active' => true,
                ]);
            }

            $keepIds[] = (int) $target->id_satuan;
        }

        // Nonaktifkan satuan non-default yang tidak dikirim lagi (tanpa delete, aman untuk histori transaksi)
        $product->satuan
            ->where('is_default', false)
            ->whereNotIn('id_satuan', $keepIds)
            ->each(fn ($s) => $s->update(['is_active' => false]));
    }

    private function formatProductForAdmin(Product $product): array
    {
        $product->loadMissing(['tags', 'satuan', 'discounts']);

        $defaultSatuan = $product->satuan
            ->where('is_active', true)
            ->firstWhere('is_default', true)
            ?? $product->satuan->where('is_active', true)->first();

        $activeWholesaleUnits = $this->getActiveWholesaleUnits($product);

        // Legacy compatibility: pilih satuan grosir terbesar untuk ditampilkan di tabel lama
        $wholesaleSatuan = $activeWholesaleUnits
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
            'barcode' => $defaultSatuan->barcode ?? null,
            'harga_jual' => $hargaJual,
            'harga_pokok' => $hargaPokok,
            'harga_jual_grosir' => $hargaJualGrosir,
            'nama_satuan_grosir' => $namaSatuanGrosir,
            'jumlah_per_satuan_grosir' => $jumlahPerSatuanGrosir,
            'satuan_grosir' => $activeWholesaleUnits
                ->map(fn ($s) => [
                    'id_satuan' => $s->id_satuan,
                    'nama_satuan' => $s->nama_satuan,
                    'barcode' => $s->barcode,
                    'jumlah_per_satuan' => (int) $s->jumlah_per_satuan,
                    'harga_jual' => (int) $s->harga_jual,
                ])
                ->values()
                ->toArray(),
            'stok' => (int) $product->stok,

            // Legacy keys (admin UI compatibility)
            'id' => $product->id_produk,
            'name' => $product->nama_produk,
            'price' => $hargaJual,
            'cost_price' => $hargaPokok,
            'wholesale' => $hargaJualGrosir,
            'wholesale_unit' => $namaSatuanGrosir,
            'wholesale_qty_per_unit' => $jumlahPerSatuanGrosir,
            // New (optional) legacy-friendly payload
            'wholesale_units' => $activeWholesaleUnits
                ->map(fn ($s) => [
                    'id_satuan' => $s->id_satuan,
                    'unit_name' => $s->nama_satuan,
                    'quantity_in_base_unit' => (int) $s->jumlah_per_satuan,
                    'price_per_unit' => (int) $s->harga_jual,
                ])
                ->values()
                ->toArray(),
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
                'barcode' => $validated['barcode'] ?? null,
                'jumlah_per_satuan' => 1,
                'harga_pokok' => (int) $validated['harga_pokok'],
                'harga_jual' => (int) $validated['harga_jual'],
                'is_default' => true,
                'is_active' => true,
            ]);

            // Grosir multi-satuan
            $this->syncWholesaleUnits(
                $product,
                (int) $validated['harga_pokok'],
                (array) ($validated['satuan_grosir'] ?? [])
            );

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
                'barcode' => $validated['barcode'] ?? null,
                'jumlah_per_satuan' => 1,
                'harga_pokok' => (int) $validated['harga_pokok'],
                'harga_jual' => (int) $validated['harga_jual'],
                'is_default' => true,
                'is_active' => true,
            ]);

            // Grosir multi-satuan
            $this->syncWholesaleUnits(
                $product,
                (int) $validated['harga_pokok'],
                (array) ($validated['satuan_grosir'] ?? [])
            );

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
