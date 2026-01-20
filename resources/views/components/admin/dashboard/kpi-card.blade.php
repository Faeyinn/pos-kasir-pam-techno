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

<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 {{ $iconClass }} rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
        </div>
    </div>
    <div class="space-y-1">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $title }}</p>
        <template x-if="loading">
            <div class="h-8 bg-slate-100 rounded animate-pulse w-2/3"></div>
        </template>
        <template x-if="!loading">
            <p class="text-lg md:text-xl font-black text-slate-900 tracking-tight" {{ $attributes }}>
                {{ $slot }}
            </p>
        </template>
    </div>
</div>
