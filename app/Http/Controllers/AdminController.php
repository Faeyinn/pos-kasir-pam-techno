<?php

namespace App\Http\Controllers;

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
        return view('pages.admin.users');
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
