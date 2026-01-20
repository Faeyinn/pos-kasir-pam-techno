<div 
    x-show="toast.show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed bottom-6 right-6 z-50"
    style="display: none;"
>
    <div 
        class="px-6 py-4 rounded-xl shadow-lg border flex items-center gap-3 min-w-[300px]"
        :class="{
            'bg-green-50 border-green-200': toast.type === 'success',
            'bg-red-50 border-red-200': toast.type === 'error',
            'bg-blue-50 border-blue-200': toast.type === 'info'
        }"
    >
        <i 
            :data-lucide="toast.type === 'success' ? 'check-circle' : toast.type === 'error' ? 'x-circle' : 'info'"
            class="w-5 h-5"
            :class="{
                'text-green-600': toast.type === 'success',
                'text-red-600': toast.type === 'error',
                'text-blue-600': toast.type === 'info'
            }"
        ></i>
        <span 
            class="font-medium"
            :class="{
                'text-green-900': toast.type === 'success',
                'text-red-900': toast.type === 'error',
                'text-blue-900': toast.type === 'info'
            }"
            x-text="toast.message"
        ></span>
    </div>
</div>
