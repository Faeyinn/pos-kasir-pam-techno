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
    <x-admin.reports.filter />
    <x-admin.reports.export-buttons />

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
