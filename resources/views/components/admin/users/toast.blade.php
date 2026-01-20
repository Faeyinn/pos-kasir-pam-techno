<div 
    x-show="notification.show" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed bottom-6 right-6 px-6 py-4 rounded-xl shadow-xl flex items-center gap-3 z-50 min-w-[320px]"
    :class="notification.type === 'success' ? 'bg-white border-l-4 border-green-500' : 'bg-white border-l-4 border-red-500'"
    style="display: none;"
>
    <div 
        class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
        :class="notification.type === 'success' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
    >
        <i :data-lucide="notification.type === 'success' ? 'check-circle' : 'alert-circle'" class="w-5 h-5"></i>
    </div>
    <div>
        <h4 
            class="font-bold text-sm" 
            :class="notification.type === 'success' ? 'text-green-800' : 'text-red-800'"
            x-text="notification.type === 'success' ? 'Berhasil!' : 'Gagal!'"
        ></h4>
        <p class="text-sm text-slate-600" x-text="notification.message"></p>
    </div>
</div>
