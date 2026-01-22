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
            ->with(['satuan' => fn ($q) => $q->where('is_default', true)])
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
        Log::info('Discount Store Request:', [
            'all_data' => $request->all(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:0',
            'target_type' => 'required|in:product,tag',
            'target_ids' => 'required|array|min:1',
            'target_ids.*' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'auto_activate' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            Log::info('Parsed Dates:', [
                'start_date_parsed' => $startDate->toDateTimeString(),
                'end_date_parsed' => $endDate->toDateTimeString()
            ]);
            
            $discount = Discount::create([
                'nama_diskon' => $validated['name'],
                'tipe_diskon' => $validated['type'] === 'percentage' ? 'persen' : 'nominal',
                'nilai_diskon' => $validated['value'],
                'target' => $validated['target_type'] === 'product' ? 'produk' : 'tag',
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'is_active' => $validated['is_active'] ?? false,
                'auto_active' => $validated['auto_activate'] ?? true
            ]);

            if ($validated['target_type'] === 'product') {
                $request->validate([
                    'target_ids.*' => 'exists:produk,id_produk',
                ]);
                $discount->products()->attach($validated['target_ids']);
            } else {
                $request->validate([
                    'target_ids.*' => 'exists:tag,id_tag',
                ]);
                $discount->tags()->attach($validated['target_ids']);
            }

            DB::commit();

            Log::info('Discount Created Successfully:', [
                'id' => $discount->id,
                'start_date_saved' => $discount->start_date,
                'end_date_saved' => $discount->end_date
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil dibuat',
                'data' => $this->formatDiscountForAdmin($discount->load(['products', 'tags']))
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Discount Store Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat diskon: ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $discount = Discount::findOrFail($id);

        Log::info('Discount Update Request:', [
            'id' => $id,
            'all_data' => $request->all(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:0',
            'target_type' => 'required|in:product,tag',
            'target_ids' => 'required|array|min:1',
            'target_ids.*' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'auto_activate' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            Log::info('Parsed Dates:', [
                'start_date_parsed' => $startDate->toDateTimeString(),
                'end_date_parsed' => $endDate->toDateTimeString()
            ]);
            
            $discount->update([
                'nama_diskon' => $validated['name'],
                'tipe_diskon' => $validated['type'] === 'percentage' ? 'persen' : 'nominal',
                'nilai_diskon' => $validated['value'],
                'target' => $validated['target_type'] === 'product' ? 'produk' : 'tag',
                'tanggal_mulai' => $startDate,
                'tanggal_selesai' => $endDate,
                'is_active' => $validated['is_active'] ?? $discount->is_active,
                'auto_active' => $validated['auto_activate'] ?? $discount->auto_active
            ]);

            if ($validated['target_type'] === 'product') {
                $request->validate([
                    'target_ids.*' => 'exists:produk,id_produk',
                ]);
                $discount->products()->sync($validated['target_ids']);
                $discount->tags()->detach();
            } else {
                $request->validate([
                    'target_ids.*' => 'exists:tag,id_tag',
                ]);
                $discount->tags()->sync($validated['target_ids']);
                $discount->products()->detach();
            }

            DB::commit();

            Log::info('Discount Updated Successfully:', [
                'id' => $discount->id,
                'start_date_saved' => $discount->fresh()->start_date,
                'end_date_saved' => $discount->fresh()->end_date
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil diupdate',
                'data' => $this->formatDiscountForAdmin($discount->load(['products', 'tags']))
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Discount Update Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate diskon: ' . $e->getMessage()
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
            
            // Detach relationships before delete
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
                'message' => 'Gagal menghapus diskon: ' . $e->getMessage()
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
        ];
    }
}
