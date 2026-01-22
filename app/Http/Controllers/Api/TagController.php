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
        $tags = Tag::orderBy('nama_tag')->get();
        
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
        // Accept both 'name' (from JS) and 'nama_tag' (legacy)
        $data = $request->all();
        if (isset($data['name'])) {
            $data['nama_tag'] = $data['name'];
        }
        
        $validated = validator($data, [
            'nama_tag' => 'required|string|max:50|unique:tag,nama_tag',
            'color' => 'required|string|max:7'
        ])->validate();

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
        
        // Accept both 'name' (from JS) and 'nama_tag' (legacy)
        $data = $request->all();
        if (isset($data['name'])) {
            $data['nama_tag'] = $data['name'];
        }
        
        $validated = validator($data, [
            'nama_tag' => 'required|string|max:50|unique:tag,nama_tag,' . $id . ',id_tag',
            'color' => 'required|string|max:7'
        ])->validate();

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
