@extends('layouts.admin')
@section('header', 'User Management')
@section('content')
    <div 
        x-data="userManager" 
        class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-sm min-h-[calc(100vh-8rem)]"
    >
        {{-- Header & Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Manajemen User</h3>
                <p class="text-slate-500 mt-1">Kelola daftar pengguna dan hak akses aplikasi</p>
            </div>
            
            <div class="flex gap-3">
                {{-- Search --}}
                <div class="relative group">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 w-5 h-5 transition-colors"></i>
                    <input 
                        type="text" 
                        x-model="search"
                        class="pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-xl text-slate-700 text-sm focus:ring-2 focus:ring-indigo-100 placeholder:text-slate-400 w-full sm:w-64 transition-all"
                        placeholder="Cari user..."
                    >
                </div>
            </div>
        </div>

        {{-- Table --}}
        <x-admin.user-table />

        {{-- Edit Role Modal --}}
        <x-admin.user-edit-role-modal />

        {{-- Toast Notification --}}
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
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userManager', () => ({
                users: @json($users),
                search: '',
                editModalOpen: false,
                currentEditUser: null,
                selectedRole: '',
                loading: false,
                notification: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                get filteredUsers() {
                    const users = !this.search ? this.users : this.users.filter(user => 
                        user.name.toLowerCase().includes(this.search.toLowerCase()) || 
                        user.email.toLowerCase().includes(this.search.toLowerCase())
                    );
                    
                    // Re-render icons when list changes
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                    
                    return users;
                },

                init() {
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                    
                    this.$watch('search', () => {
                        this.$nextTick(() => {
                            lucide.createIcons();
                        });
                    });
                },

                openEditRoleModal(user) {
                    this.currentEditUser = user;
                    this.selectedRole = user.role;
                    this.editModalOpen = true;
                },

                closeEditModal() {
                    this.editModalOpen = false;
                    setTimeout(() => {
                        this.currentEditUser = null;
                        this.selectedRole = '';
                    }, 300);
                },

                async updateRole() {
                    if (!this.currentEditUser || !this.selectedRole) return;
                    
                    this.loading = true;
                    try {
                        const response = await fetch(`/admin/users/${this.currentEditUser.id}/role`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ role: this.selectedRole })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update local data
                            const index = this.users.findIndex(u => u.id === this.currentEditUser.id);
                            if (index !== -1) {
                                this.users[index].role = this.selectedRole;
                            }
                            
                            this.showNotification(data.message);
                            this.closeEditModal();
                        } else {
                            throw new Error(data.message || 'Gagal update role');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Terjadi kesalahan sistem', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteUser(user) {
                    if (!confirm(`Apakah Anda yakin ingin menghapus user "${user.name}"?`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`/admin/users/${user.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.users = this.users.filter(u => u.id !== user.id);
                            this.showNotification(data.message || 'User berhasil dihapus', 'success');
                            
                            this.$nextTick(() => {
                                lucide.createIcons();
                            });
                        } else {
                            throw new Error(data.message || 'Gagal menghapus user');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Terjadi kesalahan saat menghapus', 'error');
                    }
                },

                showNotification(message, type = 'success') {
                    this.notification = { show: true, message, type };
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 3000);
                }
            }));
        });
    </script>
@endsection
