@extends('layouts.admin')

@section('header', 'Pengaturan Sistem')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm text-slate-500">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Dashboard</a></li>
            <li><i data-lucide="chevron-right" class="w-4 h-4"></i></li>
            <li class="text-slate-900 font-medium">Settings</li>
        </ol>
    </nav>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Konfigurasi Laporan</h3>
                <p class="text-slate-500 text-sm mt-1">Kelola tujuan pengiriman laporan otomatis ke Email Owner.</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                <i data-lucide="mail" class="w-6 h-6"></i>
            </div>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="p-8">
            @csrf
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <div class="space-y-6">
                <!-- Email Owner Input -->
                <div class="space-y-2">
                    <label for="owner_report_email" class="block text-sm font-semibold text-slate-700">
                        Email Tujuan Laporan
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="at-sign" class="w-5 h-5"></i>
                        </div>
                        <input 
                            type="email" 
                            name="owner_report_email" 
                            id="owner_report_email" 
                            value="{{ old('owner_report_email', $ownerEmail) }}"
                            class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200"
                            placeholder="contoh@gmail.com"
                            required
                        >
                    </div>
                    @error('owner_report_email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 leading-relaxed italic mt-2">
                        * Email ini digunakan sebagai alamat tujuan utama saat Bapak/Ibu menekan tombol "Kirim ke Gmail Owner" pada modul Laporan Penjualan dan Analisis Diskon.
                    </p>
                </div>

                <hr class="border-slate-100">

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="reset" class="px-6 py-3 rounded-2xl text-slate-600 font-semibold hover:bg-slate-100 transition-colors">
                        Batalkan
                    </button>
                    <button type="submit" class="flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 active:scale-95 transition-all">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-8 p-6 bg-indigo-900 rounded-3xl text-white relative overflow-hidden shadow-xl">
        <div class="absolute -right-8 -bottom-8 opacity-10">
            <i data-lucide="shield-check" class="w-48 h-48"></i>
        </div>
        <div class="relative z-10">
            <h4 class="font-bold flex items-center gap-2 mb-2 italic">
                <i data-lucide="info" class="w-5 h-5 text-indigo-300"></i>
                Keamanan Konfigurasi
            </h4>
            <p class="text-indigo-100 text-sm leading-relaxed max-w-2xl">
                Pengaturan ini bersifat kritikal. Hanya pengguna dengan tingkat akses <strong>Owner (Master)</strong> yang dapat mengakses dan mengubah konfigurasi ini untuk menjaga integritas data laporan bisnis Anda.
            </p>
        </div>
    </div>
</div>
@endsection
