<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Bergabung</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <template x-for="user in filteredUsers" :key="user.id">
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                <span x-text="user.name.charAt(0)"></span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-900" x-text="user.name"></div>
                                <div class="text-xs text-slate-500" x-text="user.email"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span 
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                            :class="{
                                'bg-purple-100 text-purple-700': user.role === 'master',
                                'bg-blue-100 text-blue-700': user.role === 'admin',
                                'bg-green-100 text-green-700': user.role === 'kasir'
                            }"
                            x-text="user.role"
                        ></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600" x-text="new Date(user.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'})"></div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button 
                                @click="openEditRoleModal(user)"
                                class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                title="Edit Role"
                            >
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </button>
                            <button 
                                @click="deleteUser(user)"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Hapus User"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
            <template x-if="filteredUsers.length === 0">
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="users" class="w-12 h-12 mb-3 text-slate-300"></i>
                            <p>Tidak ada user ditemukan</p>
                        </div>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
