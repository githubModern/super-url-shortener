<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    reports: Object,
    flaggedLinks: Array,
    stats: Object,
});

const selectedReports = ref([]);
const showReviewModal = ref(false);
const reviewingReport = ref(null);
const batchMode = ref(false);

const reviewForm = useForm({
    action: 'dismiss',
    notes: '',
});

const batchForm = useForm({
    report_ids: [],
    action: 'dismiss',
    notes: '',
});

const reasonLabels = {
    spam: 'Spam',
    phishing: 'Phishing',
    malware: 'Malware',
    violence: 'Violence',
    other: 'Other',
};

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    reviewed: 'bg-blue-100 text-blue-800',
    actioned: 'bg-red-100 text-red-800',
    dismissed: 'bg-green-100 text-green-800',
};

const toggleSelectAll = () => {
    if (selectedReports.value.length === props.reports.data.length) {
        selectedReports.value = [];
    } else {
        selectedReports.value = props.reports.data.map(r => r.id);
    }
};

const openReview = (report) => {
    reviewingReport.value = report;
    batchMode.value = false;
    reviewForm.reset();
    showReviewModal.value = true;
};

const openBatchReview = () => {
    if (selectedReports.value.length === 0) return;
    batchMode.value = true;
    batchForm.report_ids = selectedReports.value;
    batchForm.reset();
    showReviewModal.value = true;
};

const submitReview = () => {
    if (batchMode.value) {
        batchForm.post(route('admin.moderation.batch'), {
            onSuccess: () => {
                showReviewModal.value = false;
                selectedReports.value = [];
            },
        });
    } else {
        reviewForm.post(route('admin.moderation.review', reviewingReport.value.id), {
            onSuccess: () => {
                showReviewModal.value = false;
                reviewingReport.value = null;
            },
        });
    }
};

const dismissLink = (link) => {
    if (!confirm(`Dismiss all pending reports for /${link.short_code}?`)) return;
    
    // Find all reports for this link
    const linkReports = props.reports.data.filter(r => r.link_id === link.id);
    
    batchForm.report_ids = linkReports.map(r => r.id);
    batchForm.action = 'dismiss';
    batchForm.notes = 'Batch dismissed by link';
    
    batchForm.post(route('admin.moderation.batch'), {
        onSuccess: () => {
            selectedReports.value = [];
        },
    });
};
</script>

<template>
    <Head title="Moderation Queue" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</div>
                        <div class="text-sm text-gray-500">Pending Reports</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.today }}</div>
                        <div class="text-sm text-gray-500">Today's Reports</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <div class="text-2xl font-bold text-red-600">{{ stats.auto_suspended }}</div>
                        <div class="text-sm text-gray-500">Auto-Suspended</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Reports Queue -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Moderation Queue</h2>
                                    <div class="flex gap-2">
                                        <button v-if="selectedReports.length > 0" @click="openBatchReview" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                            Batch Review ({{ selectedReports.length }})
                                        </button>
                                        <Link :href="route('admin.moderation.activity-log')" class="px-3 py-1 border rounded text-sm text-gray-600 hover:bg-gray-50">
                                            Activity Log
                                        </Link>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-3">
                                                    <input type="checkbox" :checked="selectedReports.length === reports.data.length" @change="toggleSelectAll" class="rounded">
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Link</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Details</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="report in reports.data" :key="report.id" :class="{ 'bg-yellow-50 dark:bg-yellow-900/10': report.link?.auto_suspended_at }">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox" :value="report.id" v-model="selectedReports" class="rounded">
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <a :href="`/${report.link?.short_code}`" target="_blank" class="text-blue-600 hover:underline">
                                                        /{{ report.link?.short_code }}
                                                    </a>
                                                    <span v-if="report.link?.auto_suspended_at" class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded">Auto-suspended</span>
                                                </td>
                                                <td class="px-4 py-3 text-sm">{{ reasonLabels[report.reason] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ report.details || '-' }}</td>
                                                <td class="px-4 py-3">
                                                    <span :class="statusColors[report.status]" class="px-2 py-1 text-xs rounded-full">{{ report.status }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500">{{ new Date(report.created_at).toLocaleString() }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    <button v-if="report.status === 'pending'" @click="openReview(report)" class="text-blue-600 hover:text-blue-900 text-sm">Review</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div v-if="reports.data.length === 0" class="text-center py-12 text-gray-500">
                                    No pending reports. Great job!
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flagged Links -->
                    <div>
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Most Flagged Links</h3>
                                <div class="space-y-3">
                                    <div v-for="link in flaggedLinks" :key="link.id" class="border dark:border-gray-700 rounded p-3">
                                        <div class="flex justify-between items-start">
                                            <a :href="`/${link.short_code}`" target="_blank" class="text-blue-600 hover:underline font-mono text-sm">
                                                /{{ link.short_code }}
                                            </a>
                                            <span class="px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded">
                                                {{ link.reports_count }} reports
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 truncate mt-1">{{ link.destination_url }}</div>
                                        <div class="flex gap-2 mt-2">
                                            <button @click="dismissLink(link)" class="text-xs text-gray-600 hover:text-blue-600">Dismiss All</button>
                                            <span class="text-gray-300">|</span>
                                            <button @click="() => { link.is_active = false; }" class="text-xs text-red-600 hover:text-red-800">Deactivate</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div v-if="showReviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ batchMode ? `Batch Review (${selectedReports.length} reports)` : 'Review Report' }}
                    </h3>
                    
                    <div v-if="!batchMode && reviewingReport" class="mb-4 p-3 bg-gray-100 dark:bg-gray-700 rounded text-sm">
                        <div><strong>Link:</strong> /{{ reviewingReport.link?.short_code }}</div>
                        <div><strong>Reason:</strong> {{ reasonLabels[reviewingReport.reason] }}</div>
                        <div v-if="reviewingReport.details"><strong>Details:</strong> {{ reviewingReport.details }}</div>
                    </div>

                    <form @submit.prevent="submitReview">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Action</label>
                                <select v-model="reviewForm.action" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <option value="dismiss">Dismiss (No action)</option>
                                    <option value="deactivate">Deactivate Link</option>
                                    <option v-if="!batchMode" value="delete">Delete Link</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Notes</label>
                                <textarea v-model="reviewForm.notes" rows="3" class="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showReviewModal = false" class="px-4 py-2 border rounded text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit" :disabled="reviewForm.processing || batchForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                                {{ batchMode ? 'Process Batch' : 'Submit Review' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
