<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('userManager', () => ({
        users: window.__USERS_DATA__ || [],
        search: '',
        editModalOpen: false,
        currentEditUser: null,
        selectedRole: '',
        loading: false,
        notification: { show: false, message: '', type: 'success' },

        get filteredUsers() {
            const users = !this.search ? this.users : this.users.filter(user => 
                user.name.toLowerCase().includes(this.search.toLowerCase()) || 
                user.email.toLowerCase().includes(this.search.toLowerCase())
            );
            this.$nextTick(() => lucide.createIcons());
            return users;
        },

        init() {
            this.$nextTick(() => lucide.createIcons());
            this.$watch('search', () => {
                this.$nextTick(() => lucide.createIcons());
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
                    const index = this.users.findIndex(u => u.id === this.currentEditUser.id);
                    if (index !== -1) this.users[index].role = this.selectedRole;
                    
                    this.showNotification(data.message);
                    this.closeEditModal();
                } else {
                    throw new Error(data.message || 'Gagal update role');
                }
            } catch (error) {
                this.showNotification(error.message || 'Terjadi kesalahan sistem', 'error');
            } finally {
                this.loading = false;
            }
        },

        async deleteUser(user) {
            if (!confirm(`Apakah Anda yakin ingin menghapus user "${user.name}"?`)) return;

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
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    throw new Error(data.message || 'Gagal menghapus user');
                }
            } catch (error) {
                this.showNotification(error.message || 'Terjadi kesalahan saat menghapus', 'error');
            }
        },

        showNotification(message, type = 'success') {
            this.notification = { show: true, message, type };
            setTimeout(() => { this.notification.show = false; }, 3000);
        }
    }));
});
</script>
