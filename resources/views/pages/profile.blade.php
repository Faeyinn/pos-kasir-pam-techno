@extends('layouts.app')
@section('title', 'Profil Saya - Pam Techno POS')
@section('page_title', 'Profil Saya')
@section('content')
<div class="p-6 h-full overflow-y-auto custom-scrollbar">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-32 bg-blue-600"></div>
            <div class="relative flex flex-col items-center">
                <div class="w-32 h-32 bg-white p-1.5 rounded-3xl shadow-lg rotate-3 hover:rotate-0 transition-transform duration-500">
                    <div class="w-full h-full bg-blue-600 rounded-2xl flex items-center justify-center text-white text-4xl font-black uppercase">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <h2 class="text-3xl font-black text-gray-900">{{ Auth::user()->name }}</h2>
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-50 border border-blue-100 rounded-full mt-3">
                        <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i>
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">{{ Auth::user()->role === 'admin' ? 'Owner / Admin' : 'Petugas Kasir' }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1">Email Address</div>
                    <div class="font-bold text-gray-900">{{ Auth::user()->email }}</div>
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1">Bergabung Sejak</div>
                    <div class="font-bold text-gray-900">{{ Auth::user()->created_at->format('d F Y') }}</div>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('kasir') }}" class="flex-1 btn-secondary flex items-center justify-center gap-2 py-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                Kembali ke Kasir
            </a>
            <form action="{{ route('logout') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full btn-primary flex items-center justify-center gap-2 py-4 bg-red-50 border border-red-100 text-red-600 font-bold rounded-2xl hover:bg-red-100 hover:border-red-200 transition-all shadow-sm">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    Logout Sekarang
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
