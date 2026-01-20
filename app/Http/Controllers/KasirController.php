<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class KasirController extends Controller
{
    /**
     * Display the cashier (kasir) page
     */
    public function index(): View
    {
        return view('pages.kasir');
    }
}
