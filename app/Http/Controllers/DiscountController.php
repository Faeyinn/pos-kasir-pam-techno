<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DiscountController extends Controller
{
    /**
     * Display discount management page
     */
    public function index()
    {
        // Only load what's needed for display
        $discounts = Discount::with([
                'products:id,name', 
                'tags:id,name'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Only load id and name for dropdowns
        $products = Product::active()
            ->select('id', 'name', 'price')
            ->orderBy('name')
            ->get();
            
        $tags = Tag::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('pages.admin.discounts', compact('discounts', 'products', 'tags'));
    }

    /**
     * Store a new discount
     */
    public function store(Request $request)
    {
        // LOG: Debug incoming request
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
            'start_date' => 'required',  // Accept any datetime format
            'end_date' => 'required',     // Accept any datetime format  
            'is_active' => 'boolean',
            'auto_activate' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Parse datetime - handles both "2026-01-18T14:30" and "2026-01-18 14:30:00"
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            // LOG: Debug parsed dates
            Log::info('Parsed Dates:', [
                'start_date_parsed' => $startDate->toDateTimeString(),
                'end_date_parsed' => $endDate->toDateTimeString()
            ]);
            
            $discount = Discount::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'value' => $validated['value'],
                'target_type' => $validated['target_type'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => $validated['is_active'] ?? false,
                'auto_activate' => $validated['auto_activate'] ?? true
            ]);

            // Attach products or tags
            if ($validated['target_type'] === 'product') {
                $discount->products()->attach($validated['target_ids']);
            } else {
                $discount->tags()->attach($validated['target_ids']);
            }

            DB::commit();

            // LOG: Success
            Log::info('Discount Created Successfully:', [
                'id' => $discount->id,
                'start_date_saved' => $discount->start_date,
                'end_date_saved' => $discount->end_date
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil dibuat',
                'data' => $discount->load(['products', 'tags'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            // LOG: Error
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

    /**
     * Update discount
     */
    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        // LOG: Debug incoming request
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
            'start_date' => 'required',  // Accept any datetime format
            'end_date' => 'required',     // Accept any datetime format
            'is_active' => 'boolean',
            'auto_activate' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Parse datetime - handles both "2026-01-18T14:30" and "2026-01-18 14:30:00"
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            // LOG: Debug parsed dates
            Log::info('Parsed Dates:', [
                'start_date_parsed' => $startDate->toDateTimeString(),
                'end_date_parsed' => $endDate->toDateTimeString()
            ]);
            
            $discount->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'value' => $validated['value'],
                'target_type' => $validated['target_type'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => $validated['is_active'] ?? $discount->is_active,
                'auto_activate' => $validated['auto_activate'] ?? $discount->auto_activate
            ]);

            // Sync products or tags
            if ($validated['target_type'] === 'product') {
                $discount->products()->sync($validated['target_ids']);
                $discount->tags()->detach(); // Clear tags if switching type
            } else {
                $discount->tags()->sync($validated['target_ids']);
                $discount->products()->detach(); // Clear products if switching type
            }

            DB::commit();

            // LOG: Success
            Log::info('Discount Updated Successfully:', [
                'id' => $discount->id,
                'start_date_saved' => $discount->fresh()->start_date,
                'end_date_saved' => $discount->fresh()->end_date
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Diskon berhasil diupdate',
                'data' => $discount->load(['products', 'tags'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            // LOG: Error
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

    /**
     * Toggle discount status
     */
    public function toggleStatus($id)
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

    /**
     * Delete discount
     */
    public function destroy($id)
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
}
