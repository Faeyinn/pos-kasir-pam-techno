<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display role selection page for Master users
     */
    public function selection(): View|RedirectResponse
    {
        // If user is not master, redirect to home
        if (auth()->user()->role !== 'master') {
            return redirect('/');
        }
        
        return view('pages.role-selection');
    }

    /**
     * Set the active role for Master users
     */
    public function setRole(Request $request): RedirectResponse
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
