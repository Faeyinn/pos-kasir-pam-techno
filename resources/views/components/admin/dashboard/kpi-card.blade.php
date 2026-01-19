@props([
    'title',
    'icon',
    'color' => 'blue',
    'loading' => false
])

@php
$colorClasses = [
    'blue' => 'bg-blue-50 text-blue-600',
    'green' => 'bg-green-50 text-green-600',
    'purple' => 'bg-purple-50 text-purple-600',
    'red' => 'bg-red-50 text-red-600',
];

$iconClass = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 {{ $iconClass }} rounded-xl flex items-center justify-center">
            <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
        </div>
    </div>
    <div class="space-y-1">
        <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
        <template x-if="loading">
            <div class="h-8 bg-slate-100 rounded animate-pulse"></div>
        </template>
        <template x-if="!loading">
            <p class="text-2xl font-bold text-slate-900" {{ $attributes }}>
                {{ $slot }}
            </p>
        </template>
    </div>
</div>
