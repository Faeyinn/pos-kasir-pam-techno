@extends('layouts.admin')

@section('header', 'Manajemen Produk')

@section('content')
<div x-data="productManager" @toggle-tag="toggleTagList($event.detail.path, $event.detail.id)">
    <x-admin.products.header />
    <x-admin.products.table />
    <x-admin.products.add-modal />
    <x-admin.products.edit-modal />
    <x-admin.products.toast />
</div>

@push('scripts')
<script>
    window.__PRODUCTS_DATA__ = @json($products);
    window.__TAGS_DATA__ = @json($tags);
</script>
<x-admin.products.scripts />
@endpush
@endsection
