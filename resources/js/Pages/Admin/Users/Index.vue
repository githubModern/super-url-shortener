<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');
const banFilter = ref(props.filters?.ban_type || '');

const showBanModal = ref(false);
const banningUser = ref(null);
const showDeleteModal = ref(false);
const deletingUser = ref(null);

const banForm = useForm({
    ban_type: 'soft',
    ban_reason: '',
    ban_duration_days: 7,
});

const roleForm = useForm({
    role: '',
});

// Debounced search
let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), {
            search: search.value,
            role: roleFilter.value,
            ban_type: banFilter.value,
        }, { preserveState: true });
    }, 300);
});

watch([roleFilter, banFilter], () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        role: roleFilter.value,
        ban_type: banFilter.value,
    }, { preserveState: true });
});

const openBanModal = (user) => {
    banningUser.value = user;
    banForm.reset();
    showBanModal.value = true;
};

const submitBan = () => {
    banForm.post(route('admin.users.ban', banningUser.value.id), {
        onSuccess: () => {
            showBanModal.value = false;
            banningUser.value = null;
        },
    });
};

const unbanUser = (user) => {
    if (!confirm(`Unban ${user.name}?`)) return;
    
    router.post(route('admin.users.unban', user.id));
};

const updateRole = (user, role) => {
    roleForm.role = role;
    roleForm.post(route('admin.users.role', user.id));
};

const openDeleteModal = (user) => {
    deletingUser.value = user;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    router.delete(route('admin.users.destroy', deletingUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingUser.value = null;
        },
    });
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : 'N/A';

const roleColors = {
    admin: 'bg-purple-100 text-purple-800',
    user: 'bg-blue-100 text-blue-800',
    affiliate: 'bg-green-100 text-green-800',
};

const banTypeColors = {
    none: 'bg-gray-100 text-gray-800',
    soft: 'bg-yellow-100 text-yellow-800',
    hard: 'bg-red-100 text-red-800',
};
</script>

<template>
    <Head title="User Management" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</div>
                        <div class="text-sm text-gray-500">Total Users</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-purple-600">{{ stats.admins }}</div>
                        <div class="text-sm text-gray-500">Admins</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-red-600">{{ stats.banned }}</div>
                        <div class="text-sm text-gray-500">Banned Users</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">User Management</h2>

                        <!-- Filters -->
                        <div class="flex flex-wrap gap-4 mb-6">
                            <input v-model="search" type="text" placeholder="Search users..." class="px-4 py-2 border rounded dark:border-gray-600 dark:bg-gray-700" />
                            <select v-model="roleFilter" class="px-4 py-2 border rounded dark:border-gray-600 dark:bg-gray-700">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                                <option value="affiliate">Affiliate</option>
                            </select>
                            <select v-model="banFilter" class="px-4 py-2 border rounded dark:border-gray-600 dark:bg-gray-700">
                                <option value="">All Status</option>
                                <option value="none">Active</option>
                                <option value="soft">Soft Banned</option>
                                <option value="hard">Hard Banned</option>
                            </select>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Joined</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <img v-if="user.avatar" :src="user.avatar" class="w-8 h-8 rounded-full" alt="" />
                                                <div v-else class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs font-bold">
                                                    {{ user.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select v-model="user.role" @change="updateRole(user, $event.target.value)" :disabled="user.id === $page.props.auth.user.id" class="text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" :class="roleColors[user.role]">
                                                <option value="admin">Admin</option>
                                                <option value="user">User</option>
                                                <option value="affiliate">Affiliate</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span :class="banTypeColors[user.ban_type]" class="px-2 py-1 text-xs rounded-full">
                                                {{ user.ban_type === 'none' ? 'Active' : (user.ban_type === 'soft' ? 'Suspended' : 'Banned') }}
                                            </span>
                                            <div v-if="user.ban_expires_at" class="text-xs text-gray-500 mt-1">
                                                Expires: {{ formatDate(user.ban_expires_at) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button v-if="user.ban_type === 'none'" @click="openBanModal(user)" class="text-xs text-red-600 hover:text-red-800">Ban</button>
                                                <button v-else @click="unbanUser(user)" class="text-xs text-green-600 hover:text-green-800">Unban</button>
                                                <button v-if="user.id !== $page.props.auth.user.id" @click="openDeleteModal(user)" class="text-xs text-gray-600 hover:text-gray-800">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="users.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Ban Modal -->
        <div v-if="showBanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Ban User: {{ banningUser?.name }}
                    </h3>
                    <form @submit.prevent="submitBan">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ban Type</label>
                                <select v-model="banForm.ban_type" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <option value="soft">Soft Ban (Temporary)</option>
                                    <option value="hard">Hard Ban (Permanent)</option>
                                </select>
                            </div>
                            <div v-if="banForm.ban_type === 'soft'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (days)</label>
                                <input v-model="banForm.ban_duration_days" type="number" min="1" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason</label>
                                <textarea v-model="banForm.ban_reason" rows="3" required class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showBanModal = false" class="px-4 py-2 border rounded text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit" :disabled="banForm.processing" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50">
                                {{ banForm.processing ? 'Banning...' : 'Ban User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Delete User Account</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Are you sure you want to delete <strong>{{ deletingUser?.name }}</strong>? This action cannot be undone.
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mb-6">
                        Their links will be anonymized but remain active. All personal data will be permanently removed.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false" class="px-4 py-2 border rounded text-gray-700 dark:text-gray-300">Cancel</button>
                        <button @click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
