<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display user management page
     */
    public function index(): View
    {
        $users = User::orderBy('created_at', 'desc')->get();
        
        return view('pages.admin.users', compact('users'));
    }

    /**
     * Update user role
     */
    public function updateRole(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:admin,kasir,master',
        ]);

        $user = User::findOrFail($id);

        // TODO: Add authorization check - only Master can update other Masters/Admins
        
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Role user berhasil diperbarui',
            'user' => $user
        ]);
    }

    /**
     * Delete a user
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun sendiri'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}
