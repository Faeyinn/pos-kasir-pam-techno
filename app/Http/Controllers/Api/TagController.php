<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Get all tags
     */
    public function index(): JsonResponse
    {
        $tags = Tag::orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $tags
        ]);
    }
    /**
     * Store a new tag
     */
    public function store(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'color' => 'required|string|max:7'
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil ditambahkan',
            'data' => $tag
        ]);
    }

    /**
     * Update a tag
     */
    public function update(\Illuminate\Http\Request $request, $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $id,
            'color' => 'required|string|max:7'
        ]);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil diperbarui',
            'data' => $tag
        ]);
    }

    /**
     * Delete a tag
     */
    public function destroy($id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil dihapus'
        ]);
    }
}
