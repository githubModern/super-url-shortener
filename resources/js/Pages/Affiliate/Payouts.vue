<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    affiliate: { type: Object, required: true },
    payouts:   { type: Object, required: true },
});

const statusLabel = { pending: 'Pending', approved: 'Approved', rejected: 'Rejected', paid: 'Paid' };
const statusClass = { pending: 'badge--warn', approved: 'badge--info', rejected: 'badge--red', paid: 'badge--green' };

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
</script>

<template>
    <Head title="Payout History" />

    <AuthenticatedLayout>
        <template #header>Payout History</template>

        <div class="page-header">
            <Link :href="route('affiliate.index')" class="btn-ghost">← Back to Dashboard</Link>
        </div>

        <div v-if="!payouts.data?.length" class="empty">
            <p>No payout requests yet.</p>
        </div>

        <div v-else class="table-card">
            <div class="table-head">
                <span>Date</span>
                <span>Amount</span>
                <span>PayPal</span>
                <span>Status</span>
                <span>Admin Note</span>
            </div>

            <div v-for="payout in payouts.data" :key="payout.id" class="table-row">
                <span class="cell-date">{{ formatDate(payout.created_at) }}</span>
                <span class="cell-amount">${{ parseFloat(payout.amount).toFixed(2) }}</span>
                <span class="cell-email">{{ payout.paypal_email }}</span>
                <span class="badge" :class="statusClass[payout.status]">{{ statusLabel[payout.status] }}</span>
                <span class="cell-note">{{ payout.admin_note ?? '—' }}</span>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="payouts.last_page > 1" class="pagination">
            <Link v-if="payouts.prev_page_url" :href="payouts.prev_page_url" class="page-btn">← Prev</Link>
            <span class="page-info">{{ payouts.current_page }} / {{ payouts.last_page }}</span>
            <Link v-if="payouts.next_page_url" :href="payouts.next_page_url" class="page-btn">Next →</Link>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.page-header { margin-bottom: 20px; }

.table-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
}

.table-head {
    display: grid;
    grid-template-columns: 120px 100px 1fr 100px 1fr;
    padding: 10px 20px;
    font-size: 11px;
    font-weight: 600;
    color: #52525B;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.table-row {
    display: grid;
    grid-template-columns: 120px 100px 1fr 100px 1fr;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 200ms;
}

.table-row:last-child { border-bottom: none; }
.table-row:hover { background: rgba(255,255,255,0.02); }

.cell-date { font-size: 12px; color: #52525B; }
.cell-amount { font-size: 14px; font-weight: 700; color: #22C55E; }
.cell-email { font-size: 13px; color: #A1A1AA; }
.cell-note { font-size: 12px; color: #52525B; }

.badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    width: fit-content;
}

.badge--warn   { color: #F59E0B; background: rgba(245,158,11,0.1); }
.badge--info   { color: #22D3EE; background: rgba(34,211,238,0.1); }
.badge--green  { color: #22C55E; background: rgba(34,197,94,0.1); }
.badge--red    { color: #EF4444; background: rgba(239,68,68,0.1); }

.empty {
    text-align: center;
    padding: 60px;
    font-size: 14px;
    color: #52525B;
}

.btn-ghost {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    color: #71717A;
    font-size: 13px;
    text-decoration: none;
    transition: all 200ms;
}
.btn-ghost:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }

.pagination { display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 24px; }
.page-btn { font-size: 13px; color: #71717A; text-decoration: none; padding: 7px 16px; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; transition: all 200ms; }
.page-btn:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }
.page-info { font-size: 13px; color: #52525B; }
</style>
