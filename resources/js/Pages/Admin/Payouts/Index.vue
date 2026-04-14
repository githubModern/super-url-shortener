<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    payouts: { type: Object, required: true },
});

const rejectModal = ref(null);
const rejectForm = useForm({ note: '' });

const approveForm = useForm({});
const paidForm = useForm({});

const approve = (id) => {
    approveForm.post(route('admin.payouts.approve', id));
};

const markPaid = (id) => {
    paidForm.post(route('admin.payouts.mark-paid', id));
};

const openReject = (payout) => {
    rejectModal.value = payout;
    rejectForm.reset();
};

const submitReject = () => {
    rejectForm.post(route('admin.payouts.reject', rejectModal.value.id), {
        onSuccess: () => { rejectModal.value = null; },
    });
};

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
</script>

<template>
    <Head title="Admin — Payout Queue" />

    <AuthenticatedLayout>
        <template #header>Payout Queue</template>

        <div v-if="!payouts.data?.length" class="empty">
            <p>No pending payout requests.</p>
        </div>

        <div v-else class="table-card">
            <div class="table-head">
                <span>Affiliate</span>
                <span>Tier</span>
                <span>Amount</span>
                <span>PayPal Email</span>
                <span>Requested</span>
                <span>Actions</span>
            </div>

            <div v-for="payout in payouts.data" :key="payout.id" class="table-row">
                <div class="cell-user">
                    <span class="cell-user__name">{{ payout.affiliate?.user?.name }}</span>
                    <span class="cell-user__email">{{ payout.affiliate?.user?.email }}</span>
                </div>
                <span class="cell-tier">{{ payout.affiliate?.tier?.name ?? '—' }}</span>
                <span class="cell-amount">${{ parseFloat(payout.amount).toFixed(2) }}</span>
                <span class="cell-paypal">{{ payout.paypal_email }}</span>
                <span class="cell-date">{{ formatDate(payout.created_at) }}</span>
                <div class="cell-actions">
                    <button @click="approve(payout.id)" class="action-btn action-btn--approve" :disabled="approveForm.processing">
                        Approve
                    </button>
                    <button @click="openReject(payout)" class="action-btn action-btn--reject">
                        Reject
                    </button>
                    <Link :href="route('admin.payouts.audit-log', payout.id)" class="action-btn">
                        Log
                    </Link>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="payouts.last_page > 1" class="pagination">
            <Link v-if="payouts.prev_page_url" :href="payouts.prev_page_url" class="page-btn">← Prev</Link>
            <span class="page-info">{{ payouts.current_page }} / {{ payouts.last_page }}</span>
            <Link v-if="payouts.next_page_url" :href="payouts.next_page_url" class="page-btn">Next →</Link>
        </div>

        <!-- Reject Modal -->
        <div v-if="rejectModal" class="modal-overlay" @click.self="rejectModal = null">
            <div class="modal">
                <div class="modal__header">
                    <h3 class="modal__title">Reject Payout — ${{ parseFloat(rejectModal.amount).toFixed(2) }}</h3>
                    <button @click="rejectModal = null" class="modal__close">✕</button>
                </div>
                <form @submit.prevent="submitReject" class="modal__body">
                    <div class="field">
                        <label class="field__label">Rejection Reason <span style="color:#EF4444">*</span></label>
                        <textarea
                            v-model="rejectForm.note"
                            rows="4"
                            class="field__input field__textarea"
                            placeholder="Explain why this payout is being rejected…"
                            required
                        />
                        <span v-if="rejectForm.errors.note" class="field__error">{{ rejectForm.errors.note }}</span>
                    </div>
                    <div class="modal__footer">
                        <button type="button" @click="rejectModal = null" class="btn-ghost">Cancel</button>
                        <button type="submit" class="btn-danger" :disabled="rejectForm.processing">Reject Payout</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.table-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
}

.table-head {
    display: grid;
    grid-template-columns: 1.5fr 100px 100px 1fr 110px 200px;
    padding: 10px 20px;
    font-size: 11px; font-weight: 600; color: #52525B;
    text-transform: uppercase; letter-spacing: 0.07em;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.table-row {
    display: grid;
    grid-template-columns: 1.5fr 100px 100px 1fr 110px 200px;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 200ms;
}

.table-row:last-child { border-bottom: none; }
.table-row:hover { background: rgba(255,255,255,0.02); }

.cell-user { display: flex; flex-direction: column; gap: 2px; }
.cell-user__name { font-size: 13px; font-weight: 600; color: #FAFAFA; }
.cell-user__email { font-size: 11px; color: #52525B; }

.cell-tier { font-size: 12px; color: #A1A1AA; }
.cell-amount { font-size: 14px; font-weight: 700; color: #22C55E; }
.cell-paypal { font-size: 12px; color: #A1A1AA; }
.cell-date { font-size: 12px; color: #52525B; }
.cell-actions { display: flex; gap: 6px; flex-wrap: wrap; }

.action-btn {
    font-size: 11px; color: #71717A;
    padding: 4px 10px; border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.08);
    background: none; cursor: pointer;
    transition: all 200ms; white-space: nowrap;
    text-decoration: none; display: inline-flex; align-items: center;
}
.action-btn:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }
.action-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.action-btn--approve { color: #22C55E; border-color: rgba(34,197,94,0.2); }
.action-btn--approve:hover { background: rgba(34,197,94,0.08); }
.action-btn--reject { color: #EF4444; border-color: rgba(239,68,68,0.2); }
.action-btn--reject:hover { background: rgba(239,68,68,0.08); }

.empty { text-align: center; padding: 60px; font-size: 14px; color: #52525B; }

.pagination { display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 24px; }
.page-btn { font-size: 13px; color: #71717A; text-decoration: none; padding: 7px 16px; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; transition: all 200ms; }
.page-btn:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }
.page-info { font-size: 13px; color: #52525B; }

/* ── Modal ──────────────────────────────────── */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 50; display: flex; align-items: center; justify-content: center; }
.modal { background: #1A1A1A; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; width: 440px; max-width: 95vw; }
.modal__header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.07); }
.modal__title { font-size: 15px; font-weight: 600; color: #FAFAFA; }
.modal__close { background: none; border: none; color: #52525B; font-size: 16px; cursor: pointer; }
.modal__body { padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; }
.modal__footer { display: flex; justify-content: flex-end; gap: 10px; }

.field { display: flex; flex-direction: column; gap: 6px; }
.field__label { font-size: 13px; font-weight: 500; color: #A1A1AA; }
.field__input { background: #0A0A0A; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 10px 14px; font-size: 14px; color: #FAFAFA; outline: none; transition: border-color 200ms; font-family: inherit; width: 100%; }
.field__input:focus { border-color: #22D3EE; }
.field__textarea { resize: vertical; min-height: 100px; }
.field__error { font-size: 12px; color: #EF4444; }

.btn-ghost { display: inline-flex; align-items: center; padding: 9px 16px; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; color: #71717A; font-size: 13px; background: none; cursor: pointer; transition: all 200ms; }
.btn-ghost:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }

.btn-danger { display: inline-flex; align-items: center; padding: 9px 18px; background: rgba(239,68,68,0.1); color: #EF4444; font-size: 13px; font-weight: 600; border-radius: 8px; border: 1px solid rgba(239,68,68,0.25); cursor: pointer; transition: all 200ms; }
.btn-danger:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-danger:not(:disabled):hover { background: rgba(239,68,68,0.2); }
</style>
