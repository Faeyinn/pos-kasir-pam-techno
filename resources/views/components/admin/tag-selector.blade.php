{{-- Reusable Tag Selector Component --}}
@props([
    'selectedTags' => [],
    'modelName' => 'selectedTagIds'
])

<div>
    <label class="block text-sm font-medium text-slate-700 mb-3">
        Tag Produk <span class="text-red-500">*</span>
    </label>
    
    <div class="flex flex-wrap gap-2">
        <template x-for="tag in availableTags" :key="tag.id">
            <button
                type="button"
                x-on:click="toggleTagList('{{ $modelName }}', tag.id)"
                class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 border-2"
                :class="{
                    'bg-white border-slate-300 text-slate-700 hover:border-slate-400': !{{ $modelName }}.includes(tag.id),
                    'border-transparent text-white shadow-md': {{ $modelName }}.includes(tag.id)
                }"
                :style="{{ $modelName }}.includes(tag.id) ? `background-color: ${tag.color}; border-color: ${tag.color}` : ''"
            >
                <span class="flex items-center gap-2">
                    <i 
                        data-lucide="check" 
                        class="w-4 h-4"
                        x-show="{{ $modelName }}.includes(tag.id)"
                    ></i>
                    <span x-text="tag.name"></span>
                </span>
            </button>
        </template>
    </div>
    
    <p class="mt-2 text-xs text-slate-500">
        <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
        Pilih minimal 1 tag untuk produk ini
    </p>
    
    {{-- Selected Tags Preview --}}
    <div x-show="{{ $modelName }}.length > 0" class="mt-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
        <div class="text-xs font-semibold text-slate-600 mb-2">Tag Terpilih:</div>
        <div class="flex flex-wrap gap-2">
            <template x-for="tagId in {{ $modelName }}" :key="tagId">
                <span 
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-medium text-white shadow-sm"
                    :style="`background-color: ${availableTags.find(t => t.id === tagId)?.color}`"
                >
                    <span x-text="availableTags.find(t => t.id === tagId)?.name"></span>
                    <button 
                        type="button"
                        x-on:click="toggleTagList('{{ $modelName }}', tagId)"
                        class="hover:opacity-75"
                    >
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </span>
            </template>
        </div>
    </div>
</div>
