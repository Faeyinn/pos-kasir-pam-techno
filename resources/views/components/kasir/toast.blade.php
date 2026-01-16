<div class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none">
    <template x-for="n in notifications" :key="n.id">
        <div 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-4 opacity-0"
            class="pointer-events-auto min-w-[320px] max-w-md bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.12)] border border-gray-100 p-4 flex items-center gap-4 overflow-hidden relative"
        >

            <div 
                class="absolute left-0 top-0 bottom-0 w-1.5"
                :class="{
                    'bg-red-500': n.type === 'error',
                    'bg-blue-500': n.type === 'info',
                    'bg-green-500': n.type === 'success'
                }"
            ></div>

            <div 
                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                :class="{
                    'bg-red-50 text-red-500': n.type === 'error',
                    'bg-blue-50 text-blue-500': n.type === 'info',
                    'bg-green-50 text-green-500': n.type === 'success'
                }"
            >
                <template x-if="n.type === 'error'"><i data-lucide="alert-circle" class="w-5 h-5"></i></template>
                <template x-if="n.type === 'info'"><i data-lucide="info" class="w-5 h-5"></i></template>
                <template x-if="n.type === 'success'"><i data-lucide="check-circle" class="w-5 h-5"></i></template>
            </div>

            <div class="flex-1">
                <p class="text-sm font-bold text-gray-900" x-text="n.type === 'error' ? 'Gagal' : 'Informasi'"></p>
                <p class="text-xs text-gray-600 mt-0.5 leading-relaxed" x-text="n.message"></p>
            </div>

            <button @click="notifications = notifications.filter(x => x.id !== n.id)" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </template>
</div>
