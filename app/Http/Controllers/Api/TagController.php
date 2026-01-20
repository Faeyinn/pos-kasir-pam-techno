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
}
