@extends('layouts.admin')

@section('header', 'Manajemen Diskon')

@push('head-scripts')
<script>
    window.__DISCOUNTS_DATA__ = @json($discounts);
    window.__PRODUCTS_DATA__ = @json($products);
    window.__TAGS_DATA__ = @json($tags);
</script>
<x-admin.discounts.scripts />
@endpush

@section('content')
<div x-data="discountManager" x-cloak class="min-h-screen space-y-6">
    {{-- Main Management Section (Hidden on Print) --}}
    <div class="no-print space-y-6">
        <x-admin.discounts.header />
        <x-admin.discounts.table />
    </div>

    {{-- Report Section (Visible on Print) --}}
    <div class="border-t pt-3 pb-8 print:border-none print:pt-0">
        {{-- PDF Header (Only visible on Print) --}}
        <div class="hidden print:block mb-10 border-b-4 border-slate-900 pb-8">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 mb-1">LAPORAN EFEKTIVITAS DISKON</h1>
                    <p class="text-slate-500 font-bold tracking-widest uppercase text-xs">PAM TECHNO • POS ANALYTICS SYSTEM</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Tanggal Cetak</p>
                    <p class="text-sm font-bold text-slate-900">{{ now()->translatedFormat('d F Y') }}</p>
                </div>
            </div>
        </div>

        <x-admin.discounts.export-buttons />
        <x-admin.discounts.analytics />
    </div>

    <x-admin.discounts.modal :products="$products" :tags="$tags" />
</div>
@endsection

@push('scripts')
<x-admin.reports.print-styles />
@endpush
