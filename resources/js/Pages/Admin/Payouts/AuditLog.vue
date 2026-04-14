<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    payout: { type: Object, required: true },
    logs:   { type: Array,  default: () => [] },
});

const statusColor = {
    pending:  '#F59E0B',
    approved: '#22D3EE',
    rejected: '#EF4444',
    paid:     '#22C55E',
};

const formatDate = (d) => new Date(d).toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
});
</script>

<template>
    <Head :title="`Payout #${payout.id} — Audit Log`" />

    <AuthenticatedLayout>
        <template #header>Payout Audit Log</template>

        <!-- Payout summary -->
        <div class="summary-card">
            <div class="summary-row">
                <div class="summary-item">
                    <span class="summary-item__label">Affiliate</span>
                    <span class="summary-item__val">{{ payout.affiliate?.user?.name }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item__label">Amount</span>
                    <span class="summary-item__val summary-item__val--green">${{ parseFloat(payout.amount).toFixed(2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item__label">PayPal Email</span>
                    <span class="summary-item__val">{{ payout.paypal_email }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item__label">Current Status</span>
                    <span class="summary-item__val" :style="{ color: statusColor[payout.status] }">
                        {{ payout.status.charAt(0).toUpperCase() + payout.status.slice(1) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="timeline-card">
            <h3 class="section-title">Change History</h3>

            <div v-if="!logs.length" class="empty">No audit entries yet.</div>

            <div v-else class="timeline">
                <div v-for="log in logs" :key="log.id" class="timeline-item">
                    <div class="timeline-item__dot" :style="{ background: statusColor[log.new_status] ?? '#52525B' }" />
                    <div class="timeline-item__body">
                        <div class="timeline-item__header">
                            <span class="timeline-item__change">
                                <span v-if="log.old_status" class="status-pill" :style="{ color: statusColor[log.old_status] }">
                                    {{ log.old_status }}
                                </span>
                                <span v-if="log.old_status" class="arrow">→</span>
                                <span class="status-pill" :style="{ color: statusColor[log.new_status] }">
                                    {{ log.new_status }}
                                </span>
                            </span>
                            <span class="timeline-item__meta">
                                by {{ log.actor?.name ?? 'System' }} · {{ formatDate(log.created_at) }}
                            </span>
                        </div>
                        <p v-if="log.note" class="timeline-item__note">{{ log.note }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="back-row">
            <Link :href="route('admin.payouts.index')" class="btn-ghost">← Back to Payout Queue</Link>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.summary-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 20px;
}

.summary-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.summary-item__label {
    font-size: 11px;
    font-weight: 600;
    color: #52525B;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.summary-item__val {
    font-size: 14px;
    font-weight: 600;
    color: #FAFAFA;
}

.summary-item__val--green { color: #22C55E; }

/* ── Timeline ─────────────────────────────── */
.timeline-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
}

.section-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #A1A1AA;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 20px;
}

.timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    padding-left: 24px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 8px;
    bottom: 8px;
    width: 1px;
    background: rgba(255,255,255,0.06);
}

.timeline-item {
    display: flex;
    gap: 16px;
    position: relative;
    padding-bottom: 24px;
}

.timeline-item:last-child { padding-bottom: 0; }

.timeline-item__dot {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #0A0A0A;
    flex-shrink: 0;
}

.timeline-item__body {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.timeline-item__header {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.timeline-item__change {
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-pill {
    font-size: 13px;
    font-weight: 600;
    text-transform: capitalize;
}

.arrow { color: #52525B; font-size: 12px; }

.timeline-item__meta {
    font-size: 12px;
    color: #52525B;
}

.timeline-item__note {
    font-size: 13px;
    color: #71717A;
    background: rgba(255,255,255,0.02);
    border-left: 2px solid rgba(255,255,255,0.08);
    padding: 8px 12px;
    border-radius: 0 6px 6px 0;
    margin: 0;
}

.empty { font-size: 13px; color: #3F3F46; text-align: center; padding: 30px 0; }

.back-row { display: flex; }

.btn-ghost {
    display: inline-flex; align-items: center;
    padding: 8px 16px; border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px; color: #71717A; font-size: 13px;
    text-decoration: none; transition: all 200ms;
}
.btn-ghost:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }

@media (max-width: 768px) {
    .summary-row { grid-template-columns: 1fr 1fr; }
}
</style>
