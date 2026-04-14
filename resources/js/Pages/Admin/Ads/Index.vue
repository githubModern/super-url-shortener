<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    ads: Array,
});

const showCreateModal = ref(false);
const editingAd = ref(null);
const countrySearch = ref('');

const createForm = useForm({
    name: '',
    format: 'banner',
    content: '',
    target_url: '',
    target_countries: [],
    countdown_seconds: 5,
    image: null,
});

const editForm = useForm({
    name: '',
    content: '',
    target_url: '',
    target_countries: [],
    countdown_seconds: 5,
    is_active: true,
});

const countries = [
    { code: 'US', name: 'United States' },
    { code: 'GB', name: 'United Kingdom' },
    { code: 'CA', name: 'Canada' },
    { code: 'AU', name: 'Australia' },
    { code: 'DE', name: 'Germany' },
    { code: 'FR', name: 'France' },
    { code: 'IT', name: 'Italy' },
    { code: 'ES', name: 'Spain' },
    { code: 'BR', name: 'Brazil' },
    { code: 'MX', name: 'Mexico' },
    { code: 'IN', name: 'India' },
    { code: 'JP', name: 'Japan' },
    { code: 'KR', name: 'South Korea' },
    { code: 'CN', name: 'China' },
    { code: 'RU', name: 'Russia' },
    { code: 'ZA', name: 'South Africa' },
    { code: 'EG', name: 'Egypt' },
    { code: 'NG', name: 'Nigeria' },
    { code: 'SA', name: 'Saudi Arabia' },
    { code: 'AE', name: 'UAE' },
];

const filteredCountries = computed(() => {
    if (!countrySearch.value) return countries;
    return countries.filter(c => 
        c.name.toLowerCase().includes(countrySearch.value.toLowerCase()) ||
        c.code.toLowerCase().includes(countrySearch.value.toLowerCase())
    );
});

const toggleCountry = (form, code) => {
    const idx = form.target_countries.indexOf(code);
    if (idx === -1) {
        form.target_countries.push(code);
    } else {
        form.target_countries.splice(idx, 1);
    }
};

const submitCreate = () => {
    createForm.post(route('admin.ads.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const startEdit = (ad) => {
    editingAd.value = ad;
    editForm.name = ad.name;
    editForm.content = ad.content || '';
    editForm.target_url = ad.target_url || '';
    editForm.target_countries = ad.target_countries || [];
    editForm.countdown_seconds = ad.countdown_seconds;
    editForm.is_active = ad.is_active;
};

const submitEdit = () => {
    editForm.patch(route('admin.ads.update', editingAd.value.id), {
        onSuccess: () => {
            editingAd.value = null;
        },
    });
};

const deleteAd = (ad) => {
    if (confirm(`Delete ad "${ad.name}"?`)) {
        router.delete(route('admin.ads.destroy', ad.id));
    }
};

const formatCountries = (targetCountries) => {
    if (!targetCountries || targetCountries.length === 0) return 'All Countries';
    if (targetCountries.length > 3) return `${targetCountries.length} countries`;
    return targetCountries.join(', ');
};
</script>

<template>
    <Head title="Ad Management" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Advertising Management</h2>
                            <button @click="showCreateModal = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                + Create Ad
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Format</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Target</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Countries</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="ad in ads" :key="ad.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ ad.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span :class="ad.format === 'interstitial' ? 'text-purple-600' : 'text-blue-600'">{{ ad.format }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ ad.target_url || 'No target URL' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatCountries(ad.target_countries) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="ad.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs rounded-full">
                                                {{ ad.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="startEdit(ad)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                            <button @click="deleteAd(ad)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="ads.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            No ads created yet. Click "Create Ad" to get started.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create New Ad</h3>
                    <form @submit.prevent="submitCreate">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <input v-model="createForm.name" type="text" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Format</label>
                                <select v-model="createForm.format" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <option value="banner">Banner</option>
                                    <option value="interstitial">Interstitial</option>
                                </select>
                            </div>
                            <div v-if="createForm.format === 'interstitial'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Countdown Seconds</label>
                                <input v-model="createForm.countdown_seconds" type="number" min="1" max="60" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">HTML Content</label>
                                <textarea v-model="createForm.content" rows="3" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="<a href='...'><img src='...'></a>"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Target URL</label>
                                <input v-model="createForm.target_url" type="url" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Target Countries (empty = all)</label>
                                <input v-model="countrySearch" type="text" placeholder="Search countries..." class="mb-2 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
                                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-2 border rounded dark:border-gray-600">
                                    <button v-for="country in filteredCountries" :key="country.code" type="button" @click="toggleCountry(createForm, country.code)" :class="createForm.target_countries.includes(country.code) ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-2 py-1 text-xs rounded">
                                        {{ country.code }} - {{ country.name }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 border rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
                            <button type="submit" :disabled="createForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="editingAd" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Ad</h3>
                    <form @submit.prevent="submitEdit">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <input v-model="editForm.name" type="text" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" required />
                            </div>
                            <div v-if="editingAd.format === 'interstitial'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Countdown Seconds</label>
                                <input v-model="editForm.countdown_seconds" type="number" min="1" max="60" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">HTML Content</label>
                                <textarea v-model="editForm.content" rows="3" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Target URL</label>
                                <input v-model="editForm.target_url" type="url" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Target Countries</label>
                                <input v-model="countrySearch" type="text" placeholder="Search countries..." class="mb-2 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm" />
                                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-2 border rounded dark:border-gray-600">
                                    <button v-for="country in filteredCountries" :key="country.code" type="button" @click="toggleCountry(editForm, country.code)" :class="editForm.target_countries.includes(country.code) ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-2 py-1 text-xs rounded">
                                        {{ country.code }} - {{ country.name }}
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <input v-model="editForm.is_active" type="checkbox" id="is_active" class="rounded border-gray-300" />
                                <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="editingAd = null" class="px-4 py-2 border rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
                            <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
