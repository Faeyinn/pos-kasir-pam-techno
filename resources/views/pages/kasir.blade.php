@extends('layouts.app')

@section('title', 'Kasir - Pam Techno POS')
@section('page_title', 'Kasir')

@section('content')
<div 
    class="p-3 sm:p-6 h-full flex flex-col"
    x-data="kasirSystem"
    x-on:open-history.window="showHistoryModal = true; $nextTick(() => lucide.createIcons())"
    x-on:payment-type-changed.window="paymentType = $event.detail; $nextTick(() => lucide.createIcons())"
    x-on:scan-success.window="handleBarcodeScan($event.detail)"
>

    <div class="flex-1 min-h-0 flex flex-col lg:flex-row gap-4 sm:gap-6 relative">

        <div class="flex-1 flex flex-col gap-4 min-w-0 h-full">
            <x-kasir.header.index />
            <x-kasir.products.grid />
        </div>

        <x-kasir.cart.sidebar />

    </div>

    <button 
        x-on:click="mobileCartOpen = true"
        class="lg:hidden fixed bottom-6 right-6 w-16 h-16 bg-slate-900 text-white rounded-full shadow-[0_20px_50px_rgba(0,0,0,0.3)] flex items-center justify-center z-30 active:scale-90 transition-transform"
    >
        <div class="relative">
            <i data-lucide="shopping-bag" class="w-7 h-7"></i>
            <template x-if="cart.length > 0">
                <div class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-slate-900" x-text="cart.length"></div>
            </template>
        </div>
    </button>

    <x-kasir.modals.payment />
    <x-kasir.modals.receipt />
    <x-kasir.modals.confirm-clear />
    <x-kasir.modals.history />


    <x-kasir.toast />
</div>

@push('scripts')
<script src="{{ asset('js/kasir.js') }}"></script>
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #d1d5db;
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    /* Skeleton shimmer animation */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }
</style>
@endpush
@endsection
