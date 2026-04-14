<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    affiliate:  { type: Object, default: null },
    tiers:      { type: Array,  default: () => [] },
    nextTier:   { type: Object, default: null },
    minPayout:  { type: Number, default: 50 },
});

const enrollForm = useForm({});
const payoutForm = useForm({ paypal_email: '' });

const progressPercent = computed(() => {
    if (!props.affiliate || !props.nextTier) return 100;
    const visits = props.affiliate.total_visits;
    const threshold = props.nextTier.visit_threshold;
    return Math.min(100, Math.round((visits / threshold) * 100));
});

const canPayout = computed(() =>
    props.affiliate && parseFloat(props.affiliate.pending_earnings) >= props.minPayout
);

const shortUrl = (code) => `${window.location.origin}/?ref=${code}`;

const copyRef = async (code) => {
    await navigator.clipboard.writeText(shortUrl(code));
};

const statusClass = (status) => ({
    pending:  'badge--warn',
    approved: 'badge--info',
    paid:     'badge--green',
    rejected: 'badge--red',
}[status] ?? 'badge--gray');
</script>

<template>
    <Head title="Affiliate Dashboard" />

    <AuthenticatedLayout>
        <template #header>Affiliate Program</template>

        <!-- Not enrolled yet -->
        <div v-if="!affiliate" class="enroll-card">
            <div class="enroll-card__icon">💰</div>
            <h2 class="enroll-card__title">Earn with Every Click</h2>
            <p class="enroll-card__desc">
                Join our affiliate program and earn commissions for every visit
                your short links generate. Country-tiered rates mean big markets pay more.
            </p>

            <div class="tiers-preview">
                <div v-for="tier in tiers" :key="tier.id" class="tier-chip">
                    <span class="tier-chip__name">{{ tier.name }}</span>
                    <span class="tier-chip__rate">{{ tier.commission_rate }}%</span>
                </div>
            </div>

            <form @submit.prevent="enrollForm.post(route('affiliate.enroll'))">
                <button type="submit" class="btn-primary" :disabled="enrollForm.processing">
                    {{ enrollForm.processing ? 'Enrolling…' : 'Become an Affiliate' }}
                </button>
            </form>
        </div>

        <!-- Enrolled: dashboard -->
        <template v-else>
            <!-- Stat row -->
            <div class="stat-row">
                <div class="mini-stat">
                    <span class="mini-stat__val">${{ parseFloat(affiliate.total_earnings).toFixed(2) }}</span>
                    <span class="mini-stat__label">Total Earned</span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat__val">${{ parseFloat(affiliate.pending_earnings).toFixed(2) }}</span>
                    <span class="mini-stat__label">Pending</span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat__val">${{ parseFloat(affiliate.paid_earnings).toFixed(2) }}</span>
                    <span class="mini-stat__label">Paid Out</span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat__val">{{ affiliate.total_visits.toLocaleString() }}</span>
                    <span class="mini-stat__label">Total Visits</span>
                </div>
            </div>

            <div class="grid-2">
                <!-- Tier & Progress -->
                <div class="card">
                    <h3 class="card-title">Tier Status</h3>
                    <div class="tier-badge">
                        <span class="tier-badge__name">{{ affiliate.tier?.name ?? '—' }}</span>
                        <span class="tier-badge__rate">{{ affiliate.tier?.commission_rate }}% base rate</span>
                    </div>

                    <div v-if="nextTier" class="progress-wrap">
                        <div class="progress-labels">
                            <span>{{ affiliate.total_visits.toLocaleString() }} visits</span>
                            <span>{{ nextTier.visit_threshold.toLocaleString() }} for {{ nextTier.name }}</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" :style="{ width: progressPercent + '%' }" />
                        </div>
                        <p class="progress-hint">{{ progressPercent }}% toward {{ nextTier.name }}</p>
                    </div>
                    <p v-else class="top-tier-msg">You're at the top tier! 🎉</p>

                    <!-- Referral code -->
                    <div class="referral-box">
                        <span class="referral-box__label">Your Referral Code</span>
                        <div class="referral-box__row">
                            <code class="referral-box__code">{{ affiliate.referral_code }}</code>
                            <button @click="copyRef(affiliate.referral_code)" class="btn-ghost-sm">Copy Link</button>
                        </div>
                    </div>
                </div>

                <!-- Payout request -->
                <div class="card">
                    <h3 class="card-title">Request Payout</h3>

                    <div v-if="!canPayout" class="payout-locked">
                        <p>Minimum payout is <strong>${{ minPayout }}</strong>.</p>
                        <p>You currently have <strong>${{ parseFloat(affiliate.pending_earnings).toFixed(2) }}</strong> pending.</p>
                        <div class="payout-progress-track">
                            <div
                                class="payout-progress-fill"
                                :style="{ width: Math.min(100, Math.round((parseFloat(affiliate.pending_earnings) / minPayout) * 100)) + '%' }"
                            />
                        </div>
                    </div>

                    <form v-else @submit.prevent="payoutForm.post(route('affiliate.payout.request'))" class="payout-form">
                        <div class="field">
                            <label class="field__label">PayPal Email</label>
                            <input
                                v-model="payoutForm.paypal_email"
                                type="email"
                                class="field__input"
                                placeholder="you@paypal.com"
                                required
                            />
                            <span v-if="payoutForm.errors.paypal_email" class="field__error">{{ payoutForm.errors.paypal_email }}</span>
                        </div>
                        <button type="submit" class="btn-primary" :disabled="payoutForm.processing">
                            Request ${{ parseFloat(affiliate.pending_earnings).toFixed(2) }} Payout
                        </button>
                    </form>

                    <div class="payout-history-link">
                        <Link :href="route('affiliate.payouts')" class="link-muted">View payout history →</Link>
                    </div>
                </div>
            </div>

            <!-- Country rates -->
            <div v-if="affiliate.tier?.country_rates?.length" class="card mt">
                <h3 class="card-title">Your Country Rates</h3>
                <div class="rate-grid">
                    <div v-for="rate in affiliate.tier.country_rates" :key="rate.country_code" class="rate-chip">
                        <span class="rate-chip__flag">{{ rate.country_code }}</span>
                        <span class="rate-chip__val">{{ rate.commission_rate }}%</span>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>

<style scoped>
/* ── Enroll card ─────────────────────────────── */
.enroll-card {
    max-width: 520px;
    margin: 60px auto;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.enroll-card__icon { font-size: 52px; }

.enroll-card__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: #FAFAFA;
}

.enroll-card__desc {
    font-size: 14px;
    color: #71717A;
    line-height: 1.6;
}

.tiers-preview {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}

.tier-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 10px 16px;
    background: #141414;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
}

.tier-chip__name { font-size: 12px; color: #71717A; }
.tier-chip__rate { font-size: 18px; font-weight: 700; color: #22D3EE; }

/* ── Stat row ─────────────────────────────────── */
.stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

.mini-stat {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.mini-stat__val {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: #FAFAFA;
    letter-spacing: -0.02em;
}

.mini-stat__label { font-size: 11px; color: #52525B; text-transform: uppercase; letter-spacing: 0.06em; }

/* ── Grid & Cards ────────────────────────────── */
.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 24px;
}

.mt { margin-top: 0; }

.card-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #A1A1AA;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 18px;
}

/* ── Tier badge ──────────────────────────────── */
.tier-badge {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.tier-badge__name {
    font-size: 18px;
    font-weight: 700;
    color: #22D3EE;
}

.tier-badge__rate { font-size: 13px; color: #71717A; }

/* ── Progress ────────────────────────────────── */
.progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #52525B;
    margin-bottom: 6px;
}

.progress-track {
    height: 6px;
    background: rgba(255,255,255,0.06);
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #22D3EE;
    border-radius: 3px;
    transition: width 600ms ease;
}

.progress-hint { font-size: 11px; color: #52525B; margin-top: 6px; }

.top-tier-msg { font-size: 14px; color: #22C55E; }

/* ── Referral box ────────────────────────────── */
.referral-box {
    margin-top: 20px;
    padding: 14px;
    background: rgba(34,211,238,0.04);
    border: 1px solid rgba(34,211,238,0.12);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.referral-box__label { font-size: 11px; color: #52525B; text-transform: uppercase; letter-spacing: 0.06em; }

.referral-box__row { display: flex; align-items: center; gap: 10px; }

.referral-box__code {
    font-family: monospace;
    font-size: 15px;
    font-weight: 700;
    color: #22D3EE;
    letter-spacing: 0.05em;
}

/* ── Payout form ─────────────────────────────── */
.payout-locked { display: flex; flex-direction: column; gap: 8px; }
.payout-locked p { font-size: 13px; color: #71717A; }
.payout-locked strong { color: #FAFAFA; }

.payout-progress-track {
    height: 4px;
    background: rgba(255,255,255,0.06);
    border-radius: 2px;
    margin-top: 8px;
    overflow: hidden;
}

.payout-progress-fill {
    height: 100%;
    background: #22C55E;
    border-radius: 2px;
}

.payout-form { display: flex; flex-direction: column; gap: 16px; }

.field { display: flex; flex-direction: column; gap: 6px; }
.field__label { font-size: 13px; font-weight: 500; color: #A1A1AA; }
.field__input {
    background: #0A0A0A;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    color: #FAFAFA;
    outline: none;
    transition: border-color 200ms;
}
.field__input:focus { border-color: #22D3EE; }
.field__error { font-size: 12px; color: #EF4444; }

.payout-history-link { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.05); }

/* ── Country rates ───────────────────────────── */
.rate-grid { display: flex; gap: 8px; flex-wrap: wrap; }

.rate-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 6px;
}

.rate-chip__flag { font-size: 12px; color: #71717A; font-weight: 600; }
.rate-chip__val { font-size: 13px; color: #22D3EE; font-weight: 700; }

/* ── Buttons ─────────────────────────────────── */
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 22px;
    background: #22D3EE;
    color: #0A0A0A;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: opacity 200ms;
}

.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-primary:not(:disabled):hover { opacity: 0.85; }

.btn-ghost-sm {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.1);
    background: none;
    color: #71717A;
    cursor: pointer;
    transition: all 200ms;
}
.btn-ghost-sm:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }

.link-muted { font-size: 13px; color: #52525B; text-decoration: none; }
.link-muted:hover { color: #A1A1AA; }

@media (max-width: 768px) {
    .stat-row { grid-template-columns: 1fr 1fr; }
    .grid-2 { grid-template-columns: 1fr; }
}
</style>
