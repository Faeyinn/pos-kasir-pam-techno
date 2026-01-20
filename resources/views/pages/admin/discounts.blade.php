@extends('layouts.admin')

@section('header', 'Manajemen Diskon')

@section('content')
<div x-data="discountManager" class="min-h-screen space-y-6">
    <x-admin.discounts.header />
    <x-admin.discounts.table />
    <x-admin.discounts.analytics />
    <x-admin.discounts.modal :products="$products" :tags="$tags" />
</div>
@endsection

@push('scripts')
<script>
    window.__DISCOUNTS_DATA__ = @json($discounts);
    window.__PRODUCTS_DATA__ = @json($products);
    window.__TAGS_DATA__ = @json($tags);
</script>
<x-admin.discounts.scripts />
@endpush
