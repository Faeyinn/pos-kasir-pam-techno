<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $ownerEmail = Setting::get('owner_report_email', config('mail.owner_email'));
        
        return view('pages.admin.settings', compact('ownerEmail'));
    }

    /**
     * Update the application settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'owner_report_email' => 'required|email|max:255',
        ], [
            'owner_report_email.required' => 'Email owner wajib diisi.',
            'owner_report_email.email' => 'Format email tidak valid.',
        ]);

        Setting::updateOrCreate(
            ['key' => 'owner_report_email'],
            [
                'value' => $request->owner_report_email,
                'description' => 'Email tujuan untuk pengiriman laporan otomatis (Marketing & Sales).',
            ]
        );

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
