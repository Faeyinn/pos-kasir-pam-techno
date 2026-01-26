@extends('layouts.admin')

@section('header', 'Laporan & Analisis Penjualan')

@push('head-scripts')
<script>
    window.__TAGS_DATA__ = @json($tags);
</script>
<x-admin.reports.scripts />
@endpush

@section('content')
<div x-data="reportManager" x-cloak class="min-h-screen">
    {{-- PDF Header (Only visible on Print) --}}
    <div class="hidden print:block mb-10 border-b-4 border-slate-900 pb-8">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-black text-slate-900 mb-1">LAPORAN ANALISIS PENJUALAN</h1>
                <p class="text-slate-500 font-bold tracking-widest uppercase text-xs">PAM TECHNO • POS ANALYTICS SYSTEM</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Tanggal Cetak</p>
                <p class="text-sm font-bold text-slate-900">{{ now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>
    </div>

    <x-admin.reports.filter />

    <div x-show="!loading" x-transition.opacity>
        <x-admin.reports.summary />
        <x-admin.reports.charts />
        
        <div class="mt-6">
            <x-admin.reports.heatmap />
        </div>
        
        <x-admin.reports.table />
    </div>

    <x-admin.reports.loading />
</div>
@endsection

@push('scripts')
<x-admin.reports.print-styles />
@endpush
