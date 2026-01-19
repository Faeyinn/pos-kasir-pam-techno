<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('pages.admin.dashboard');
    }

    public function products()
    {
        return view('pages.admin.products');
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('pages.admin.users', compact('users'));
    }

    public function discounts()
    {
        return view('pages.admin.discounts');
    }

    public function reports()
    {
        return view('pages.admin.reports');
    }

    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,kasir,master',
        ]);

        $user = User::findOrFail($id);

        // Prevent modifying own role or Master role if needed, but for now allow strict role updates
        // Ideally, only Master can update other Masters or Admins.
        
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Role user berhasil diperbarui',
            'user' => $user
        ]);
    }

    public function destroy($id)
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

    public function roleSelection()
    {
        // If user is not master, they shouldn't be here, but middleware might handle it or we redirect
        if (auth()->user()->role !== 'master') {
            return redirect('/');
        }
        return view('pages.role-selection');
    }

    public function setRole(Request $request)
    {
        $request->validate([
            'role' => 'required|in:admin,kasir',
        ]);

        session(['active_role' => $request->role]);

        if ($request->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('kasir');
    }
}
