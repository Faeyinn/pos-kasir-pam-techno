@extends('layouts.admin')
@section('header', 'User Management')

@push('head-scripts')
<script>
    window.__USERS_DATA__ = @json($users);
</script>
<x-admin.users.scripts />
@endpush

@section('content')
<div x-data="userManager" x-cloak class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-sm min-h-[calc(100vh-8rem)]">
    <x-admin.users.header />
    <x-admin.users.table />
    <x-admin.users.edit-modal />
    <x-admin.users.toast />
</div>
@endsection
