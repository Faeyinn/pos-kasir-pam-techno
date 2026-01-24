<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DiscountController extends Controller
{
    public function index(): View|JsonResponse
    {
        $discounts = Discount::with(['products', 'tags'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Discount $d) => $this->formatDiscountForAdmin($d))
            ->values();

        $products = Product::active()
            ->with(['satuan' => fn ($q) => $q->where('is_default', true), 'tags'])
            ->orderBy('nama_produk')
            ->get()
            ->map(fn (Product $p) => $this->formatProductForDiscountPicker($p))
            ->values();

        $tags = Tag::orderBy('nama_tag')
            ->get()
            ->map(fn (Tag $t) => [
                'id' => $t->id_tag,
                'name' => $t->nama_tag,
                'slug' => $t->slug,
                'color' => $t->color,
            ])
            ->values();

        if (request()->is('api/*') || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'discounts' => $discounts,
                    'products' => $products,
                    'tags' => $tags,
                ],
            ]);
        }

        return view('pages.admin.discounts', compact('discounts', 'products', 'tags'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:0',
            'target_ids' => 'required|array|min:1',
            'target_ids.*' => 'required|integer|exists:produk,id_produk',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'auto_activate' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            $discount = Discount::create([
                'nama_diskon' => $validated['name'],
                'tipe_diskon' => $validated['type'] === 'percentage' ? 'persen' : 'nominal',
                'nilai_diskon' => $validated['value'],
                'target' => 'produk',
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'is_active' => $validated['is_active'] ?? true,
                'auto_active' => $validated['auto_activate'] ?? true
            ]);

            $discount->products()->sync($validated['target_ids']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil dibuat',
                'data' => $this->formatDiscountForAdmin($discount->load(['products', 'tags']))
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Discount Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat diskon'
            ], 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $discount = Discount::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:0',
            'target_ids' => 'required|array|min:1',
            'target_ids.*' => 'required|integer|exists:produk,id_produk',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'auto_activate' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            $discount->update([
                'nama_diskon' => $validated['name'],
                'tipe_diskon' => $validated['type'] === 'percentage' ? 'persen' : 'nominal',
                'nilai_diskon' => $validated['value'],
                'target' => 'produk',
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'is_active' => $validated['is_active'] ?? $discount->is_active,
                'auto_active' => $validated['auto_activate'] ?? $discount->auto_active
            ]);

            $discount->products()->sync($validated['target_ids']);
            $discount->tags()->detach();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil diupdate',
                'data' => $this->formatDiscountForAdmin($discount->load(['products', 'tags']))
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Discount Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate diskon'
            ], 400);
        }
    }

    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $discount = Discount::findOrFail($id);
            $discount->is_active = !$discount->is_active;
            $discount->save();

            return response()->json([
                'success' => true,
                'message' => $discount->is_active ? 'Diskon berhasil diaktifkan' : 'Diskon berhasil dinonaktifkan',
                'data' => $discount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $discount = Discount::findOrFail($id);
            $discount->products()->detach();
            $discount->tags()->detach();
            $discount->delete();

            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus diskon'
            ], 400);
        }
    }

    private function formatDiscountForAdmin(Discount $discount): array
    {
        $type = $discount->tipe_diskon === 'persen' ? 'percentage' : 'fixed';
        $targetType = $discount->target === 'produk' ? 'product' : 'tag';

        return [
            'id' => $discount->id_diskon,
            'name' => $discount->nama_diskon,
            'type' => $type,
            'value' => (int) $discount->nilai_diskon,
            'target_type' => $targetType,
            'start_date' => $discount->tanggal_mulai,
            'end_date' => $discount->tanggal_selesai,
            'is_active' => (bool) $discount->is_active,
            'auto_activate' => (bool) $discount->auto_active,
            'status' => $discount->status,
            'products' => $discount->products
                ->map(fn (Product $p) => [
                    'id' => $p->id_produk,
                    'name' => $p->nama_produk,
                ])
                ->values(),
            'tags' => $discount->tags
                ->map(fn (Tag $t) => [
                    'id' => $t->id_tag,
                    'name' => $t->nama_tag,
                ])
                ->values(),
        ];
    }

    private function formatProductForDiscountPicker(Product $product): array
    {
        $defaultUnit = $product->satuan->first();

        return [
            'id' => $product->id_produk,
            'name' => $product->nama_produk,
            'price' => (int) ($defaultUnit?->harga_jual ?? 0),
            'tags' => $product->tags->map(fn($t) => [
                'id' => $t->id_tag,
                'name' => $t->nama_tag
            ])->values(),
        ];
    }
}
