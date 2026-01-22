<div 
    x-data="tagManagerData()"
    x-on:open-manage-tags.window="
        view = 'list';
        errorMessage = '';
        fetchTags();
        $dispatch('open-tag-manager');
    "
    x-on:open-add-tag.window="
        openForm();
        setTimeout(() => $dispatch('open-tag-manager'), 50);
    "
>
    <!-- Tag Manager Modal -->
    <x-ui.modal name="tag-manager" max-width="lg">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-900" x-text="modalTitle">Kelola Tag</h3>
                <button 
                    type="button"
                    @click="closeManager()"
                    class="text-slate-400 hover:text-slate-600 transition-colors"
                >
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Error Alert -->
            <div 
                x-show="errorMessage"
                x-cloak
                class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3"
            >
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                <span class="text-sm text-red-700" x-text="errorMessage"></span>
            </div>

            <!-- Content -->
            <div x-show="view === 'list'" x-transition>
                <!-- Search & Add Button -->
                <div class="flex gap-2 mb-4">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input 
                            type="text"
                            x-model="search"
                            class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="Cari tag..."
                        >
                    </div>
                    <button 
                        @click="openForm()"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                    >
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Tambah
                    </button>
                </div>

                <!-- Tag List -->
                <div class="grid grid-cols-2 gap-3 max-h-[400px] overflow-y-auto p-1">
                    <template x-for="tag in filteredTags" :key="tag.id">
                        <div class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-xl hover:border-indigo-300 hover:shadow-sm transition-all group">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <span 
                                    class="w-3 h-3 rounded-full flex-shrink-0"
                                    :style="`background-color: ${tag.color}`"
                                ></span>
                                <span class="font-medium text-slate-700 truncate text-sm" x-text="tag.name"></span>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0 bg-white pl-2">
                                <button 
                                    @click="editTag(tag)"
                                    class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                    title="Edit"
                                >
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                </button>
                                <button 
                                    @click="deleteTag(tag.id)"
                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Hapus"
                                >
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="filteredTags.length === 0" class="col-span-2 text-center py-8 text-slate-500 text-sm">
                        Tag tidak ditemukan
                    </div>
                </div>
            </div>

            <!-- Form (Add/Edit) -->
            <form x-show="view === 'form'" @submit.prevent="submitForm" x-transition>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Nama Tag <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text"
                            x-model="form.name"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Contoh: Makanan, Minuman, Promo"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Warna Label
                        </label>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <template x-for="color in presetColors" :key="color">
                                <button 
                                    type="button"
                                    @click="form.color = color"
                                    class="w-8 h-8 rounded-full border-2 transition-transform hover:scale-110 focus:outline-none"
                                    :class="form.color === color ? 'border-slate-600 scale-110' : 'border-transparent'"
                                    :style="`background-color: ${color}`"
                                ></button>
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                type="color"
                                x-model="form.color"
                                class="h-9 w-14 p-0 border border-slate-300 rounded overflow-hidden cursor-pointer"
                            >
                            <span class="text-sm text-slate-500" x-text="form.color"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6 pt-6 border-t border-slate-200">
                    <button 
                        type="button"
                        @click="view = 'list'; errorMessage = ''"
                        class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-medium transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                        :disabled="loading"
                    >
                        <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="form.id ? 'Simpan Perubahan' : 'Tambah Tag'"></span>
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <script>
    function tagManagerData() {
        return {
            view: 'list', // list, form
            tags: [],
            search: '',
            loading: false,
            errorMessage: '',
            form: {
                id: null,
                name: '',
                color: '#6366f1' // Default Indigo
            },
            presetColors: [
                '#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', 
                '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e', '#64748b'
            ],

            init() {
                // Initialize from window data if available
                if (Array.isArray(window.__TAGS_DATA__) && window.__TAGS_DATA__.length > 0) {
                    this.tags = window.__TAGS_DATA__;
                }
                
                // Watch for changes and re-render icons
                this.$watch('view', () => this.$nextTick(() => lucide.createIcons()));
                this.$watch('tags', () => this.$nextTick(() => lucide.createIcons()));
                
                // Listen for tags updates from productManager
                document.addEventListener('tags-updated', (e) => {
                    if (Array.isArray(e.detail)) {
                        this.tags = e.detail;
                    }
                });
            },

            get filteredTags() {
                // Ensure we have an array to work with
                const tagsArray = Array.isArray(this.tags) ? this.tags : [];
                
                // Ensure valid IDs and remove duplicates
                const seenIds = new Set();
                const validTags = tagsArray.filter(t => {
                    if (!t || t.id === undefined || t.id === null) return false;
                    if (seenIds.has(t.id)) return false;
                    seenIds.add(t.id);
                    return true;
                });
                
                if (!this.search) return validTags;
                const q = this.search.toLowerCase();
                return validTags.filter(t => t.name && t.name.toLowerCase().includes(q));
            },

            get modalTitle() {
                if (this.view === 'list') return 'Kelola Tag';
                return this.form.id ? 'Edit Tag' : 'Tambah Tag Baru';
            },

            async fetchTags() {
                try {
                    const res = await fetch('/api/admin/tags');
                    const json = await res.json();
                    if (json.success && Array.isArray(json.data)) {
                        this.tags = json.data;
                        // Sync window data
                        window.__TAGS_DATA__ = this.tags;
                        // Tell main productManager to update its availableTags
                        this.$dispatch('tags-updated', this.tags);
                    }
                    // If not successful, keep existing tags
                } catch (e) {
                    console.error('Failed to fetch tags', e);
                    // Keep existing tags on error
                }
            },

            openForm() {
                this.form = {
                    id: null,
                    name: '',
                    color: this.presetColors[Math.floor(Math.random() * this.presetColors.length)]
                };
                this.view = 'form';
                this.errorMessage = '';
            },

            editTag(tag) {
                this.form = { ...tag };
                this.view = 'form';
                this.errorMessage = '';
            },

            closeManager() {
                this.$dispatch('close-tag-manager');
                this.search = '';
                this.errorMessage = '';
            },

            async submitForm() {
                this.loading = true;
                this.errorMessage = '';

                try {
                    const url = this.form.id 
                        ? `/api/admin/tags/${this.form.id}`
                        : '/api/admin/tags';
                    
                    const method = this.form.id ? 'PUT' : 'POST';

                    const res = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.form)
                    });

                    const json = await res.json();

                    if (json.success) {
                        // Refresh tags
                        await this.fetchTags();
                        this.view = 'list';
                    } else {
                        /**
                         * If the error is an object (validation errors), convert to string array or join
                         * If it's a simple message, display it.
                         */
                        if (json.errors) {
                            // Extract first error from each field
                            this.errorMessage = Object.values(json.errors).map(e => e[0]).join(', ');
                        } else {
                            this.errorMessage = json.message || 'Terjadi kesalahan';
                        }
                    }
                } catch (e) {
                    this.errorMessage = 'Gagal menghubungi server';
                } finally {
                    this.loading = false;
                }
            },

            async deleteTag(id) {
                if (!confirm('Apakah Anda yakin ingin menghapus tag ini?')) return;

                try {
                    const res = await fetch(`/api/admin/tags/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const json = await res.json();
                    
                    if (json.success) {
                        await this.fetchTags();
                    } else {
                        alert(json.message);
                    }
                } catch (e) {
                    alert('Gagal menghapus tag');
                }
            }
        };
    }
    </script>
</div>
