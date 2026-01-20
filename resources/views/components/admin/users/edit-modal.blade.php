<div
    x-show="editModalOpen"
    style="display: none;"
    class="relative z-50"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
>
    <!-- Background backdrop -->
    <div
        x-show="editModalOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
    ></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel -->
            <div
                x-show="editModalOpen"
                @click.outside="closeEditModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md"
            >
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i data-lucide="shield" class="h-6 w-6 text-indigo-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title">Edit Role User</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 mb-4">
                                    Ubah role untuk user <span class="font-bold text-slate-700" x-text="currentEditUser?.name"></span>.
                                </p>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Role</label>
                                        <select 
                                            x-model="selectedRole" 
                                            class="w-full rounded-lg border-slate-300 py-2.5 text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="" disabled>Pilih Role</option>
                                            <option value="kasir">Kasir</option>
                                            <option value="admin">Admin</option>
                                            <!-- prevent master selection for now unless user is master, logic handled in controller validation too -->
                                            <template x-if="currentEditUser?.role === 'master' || {{ json_encode(auth()->user()->role === 'master') }}">
                                                 <option value="master">Master</option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button 
                        type="button" 
                        @click="updateRole"
                        :disabled="loading"
                        class="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed items-center gap-2"
                    >
                        <i x-show="loading" data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                    <button 
                        type="button" 
                        @click="closeEditModal"
                        class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
