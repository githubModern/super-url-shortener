<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    link: { type: Object, required: true },
    analytics: {
        type: Object,
        default: () => ({
            total_clicks: 0,
            period: 'all',
            clicks_by_country: [],
            clicks_by_device: [],
            clicks_by_browser: [],
            clicks_by_referrer: [],
            clicks_over_time: [],
        }),
    },
});

const copied       = ref(false);
const embedCopied  = ref(false);
const showEmbed    = ref(false);
const showReportModal = ref(false);
const reportSubmitted = ref(false);
const reportError = ref('');

const shortUrl = computed(() => props.link.short_url ?? `${window.location.origin}/${props.link.short_code}`);

const embedCode = computed(() =>
    `<a href="${shortUrl.value}" target="_blank" rel="noopener">${shortUrl.value}</a>`
);

const copyUrl = async () => {
    await navigator.clipboard.writeText(shortUrl.value);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const copyEmbed = async () => {
    await navigator.clipboard.writeText(embedCode.value);
    embedCopied.value = true;
    setTimeout(() => { embedCopied.value = false; }, 2000);
};

const deleteLink = () => {
    if (!confirm('Delete this link permanently?')) return;
    router.delete(route('links.destroy', props.link.id));
};

const setPeriod = (p) => {
    router.get(route('links.show', props.link.id), { period: p }, { preserveState: true, preserveScroll: true });
};

const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

const topCountries = computed(() => (props.analytics.clicks_by_country || []).slice(0, 8));
const topReferrers = computed(() => (props.analytics.clicks_by_referrer || []).slice(0, 5));

const deviceColors = { desktop: '#22D3EE', mobile: '#22C55E', tablet: '#F59E0B', bot: '#A78BFA' };

const periods = [
    { key: 'today', label: 'Today' },
    { key: 'week',  label: '7 Days' },
    { key: 'month', label: '30 Days' },
    { key: 'all',   label: 'All Time' },
];

const activePeriod = computed(() => props.analytics.period ?? 'all');

// Social share URLs
const twitterUrl   = computed(() => `https://twitter.com/intent/tweet?url=${encodeURIComponent(shortUrl.value)}`);
const whatsappUrl  = computed(() => `https://wa.me/?text=${encodeURIComponent(shortUrl.value)}`);
const telegramUrl  = computed(() => `https://t.me/share/url?url=${encodeURIComponent(shortUrl.value)}`);
const linkedinUrl  = computed(() => `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shortUrl.value)}`);
const facebookUrl  = computed(() => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shortUrl.value)}`);

// QR download URLs
const qrSvgUrl = computed(() => route('links.qr', { link: props.link.id, format: 'svg' }));
const qrPngUrl = computed(() => route('links.qr', { link: props.link.id, format: 'png' }));

// Max value for bar charts
const maxDevice  = computed(() => Math.max(1, ...(props.analytics.clicks_by_device  || []).map(d => d.total)));
const maxCountry = computed(() => Math.max(1, ...(props.analytics.clicks_by_country || []).map(d => d.total)));
const maxRef     = computed(() => Math.max(1, ...(topReferrers.value).map(d => d.total)));

// Report form
const reportForm = useForm({
    reason: 'spam',
    details: '',
});

const reasonOptions = [
    { value: 'spam', label: 'Spam / Unwanted content' },
    { value: 'phishing', label: 'Phishing / Fraud' },
    { value: 'malware', label: 'Malware / Virus' },
    { value: 'violence', label: 'Violence / Harmful content' },
    { value: 'other', label: 'Other' },
];

const submitReport = () => {
    reportError.value = '';
    reportSubmitted.value = false;
    
    reportForm.post(route('links.report', props.link.id), {
        preserveScroll: true,
        onSuccess: () => {
            reportSubmitted.value = true;
            reportForm.reset();
            setTimeout(() => {
                showReportModal.value = false;
                reportSubmitted.value = false;
            }, 2000);
        },
        onError: (errors) => {
            reportError.value = errors.error || 'Failed to submit report. Please try again.';
        },
    });
};
</script>

<template>
    <Head :title="`/${link.short_code} — Analytics`" />

    <AuthenticatedLayout>
        <template #header>Link Analytics</template>

        <!-- Hero: short URL + actions -->
        <div class="link-hero">
            <div class="link-hero__left">
                <div class="link-hero__short">
                    <a :href="shortUrl" target="_blank" class="short-anchor">{{ shortUrl }}</a>
                    <button @click="copyUrl" class="copy-pill">{{ copied ? '✓ Copied' : 'Copy' }}</button>
                </div>
                <a :href="link.destination_url" target="_blank" rel="noopener" class="dest-anchor">{{ link.destination_url }}</a>
                <div class="link-meta">
                    <span class="meta-badge" :class="link.is_active ? 'meta-badge--active' : 'meta-badge--inactive'">
                        {{ link.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span v-if="link.campaign_tag" class="meta-badge meta-badge--tag">{{ link.campaign_tag }}</span>
                    <span class="meta-created">Created {{ formatDate(link.created_at) }}</span>
                </div>
            </div>
            <div class="link-hero__actions">
                <Link :href="route('links.edit', link.id)" class="btn-ghost">Edit</Link>
                <button @click="deleteLink" class="btn-danger">Delete</button>
                <button @click="showReportModal = true" class="text-xs text-gray-500 hover:text-red-500 mt-2">Report Link</button>
            </div>
        </div>

        <!-- Share + QR row (Stories 3.4, 3.5, 3.6) -->
        <div class="share-row">
            <!-- Social sharing -->
            <div class="share-group">
                <span class="share-label">Share</span>
                <a :href="twitterUrl"  target="_blank" rel="noopener" class="share-btn share-btn--twitter" title="Share on Twitter/X">𝕏</a>
                <a :href="whatsappUrl" target="_blank" rel="noopener" class="share-btn share-btn--whatsapp" title="Share on WhatsApp">💬</a>
                <a :href="telegramUrl" target="_blank" rel="noopener" class="share-btn share-btn--telegram" title="Share on Telegram">✈️</a>
                <a :href="linkedinUrl" target="_blank" rel="noopener" class="share-btn share-btn--linkedin" title="Share on LinkedIn">in</a>
                <a :href="facebookUrl" target="_blank" rel="noopener" class="share-btn share-btn--facebook" title="Share on Facebook">f</a>
            </div>

            <!-- QR download -->
            <div class="share-group">
                <span class="share-label">QR Code</span>
                <a :href="qrSvgUrl" class="qr-btn">SVG</a>
                <a :href="qrPngUrl" class="qr-btn">PNG</a>
            </div>

            <!-- Embed code -->
            <div class="share-group">
                <span class="share-label">Embed</span>
                <button @click="showEmbed = !showEmbed" class="qr-btn">{{ showEmbed ? 'Hide' : 'Get Code' }}</button>
            </div>
        </div>

        <!-- Embed code panel -->
        <div v-if="showEmbed" class="embed-panel">
            <code class="embed-code">{{ embedCode }}</code>
            <button @click="copyEmbed" class="copy-pill" style="flex-shrink:0">{{ embedCopied ? '✓ Copied' : 'Copy' }}</button>
        </div>

        <!-- Period filter (Story 3.9b) -->
        <div class="period-bar">
            <button
                v-for="p in periods"
                :key="p.key"
                @click="setPeriod(p.key)"
                class="period-btn"
                :class="activePeriod === p.key ? 'period-btn--active' : ''"
            >{{ p.label }}</button>
        </div>

        <!-- Stat row -->
        <div class="stat-row">
            <div class="mini-stat">
                <span class="mini-stat__val">{{ (analytics.total_clicks ?? 0).toLocaleString() }}</span>
                <span class="mini-stat__label">Clicks ({{ periods.find(p=>p.key===activePeriod)?.label }})</span>
            </div>
            <div class="mini-stat">
                <span class="mini-stat__val">{{ topCountries.length }}</span>
                <span class="mini-stat__label">Countries</span>
            </div>
            <div class="mini-stat">
                <span class="mini-stat__val">{{ topReferrers.length }}</span>
                <span class="mini-stat__label">Referrers</span>
            </div>
            <div class="mini-stat">
                <span class="mini-stat__val">{{ link.expires_at ? formatDate(link.expires_at) : '∞' }}</span>
                <span class="mini-stat__label">Expires</span>
            </div>
        </div>

        <!-- Analytics grid -->
        <div class="analytics-grid">
            <!-- Devices -->
            <div class="analytics-card">
                <h3 class="card-title">Devices</h3>
                <div v-if="!analytics.clicks_by_device?.length" class="no-data">No data yet</div>
                <div v-else class="bar-list">
                    <div v-for="row in analytics.clicks_by_device" :key="row.device_type" class="bar-item">
                        <div class="bar-item__label">
                            <span>{{ row.device_type || 'Unknown' }}</span>
                            <span class="bar-item__count">{{ row.total }}</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" :style="{ width: `${Math.round((row.total / maxDevice) * 100)}%`, background: deviceColors[row.device_type] ?? '#71717A' }" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Countries with flag emojis (Story 3.10) -->
            <div class="analytics-card">
                <h3 class="card-title">Top Countries</h3>
                <div v-if="!topCountries.length" class="no-data">No data yet</div>
                <div v-else class="bar-list">
                    <div v-for="row in topCountries" :key="row.country_code" class="bar-item">
                        <div class="bar-item__label">
                            <span>
                                <span class="country-flag">{{ row.flag }}</span>
                                {{ row.country_code || 'Unknown' }}
                            </span>
                            <span class="bar-item__count">{{ row.total }}</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" :style="{ width: `${Math.round((row.total / maxCountry) * 100)}%`, background: '#22D3EE' }" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referrers -->
            <div class="analytics-card">
                <h3 class="card-title">Top Referrers</h3>
                <div v-if="!topReferrers.length" class="no-data">No referrer data yet</div>
                <div v-else class="bar-list">
                    <div v-for="row in topReferrers" :key="row.referrer_domain" class="bar-item">
                        <div class="bar-item__label">
                            <span>{{ row.referrer_domain || 'Direct' }}</span>
                            <span class="bar-item__count">{{ row.total }}</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" :style="{ width: `${Math.round((row.total / maxRef) * 100)}%`, background: '#F59E0B' }" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Browsers -->
            <div class="analytics-card">
                <h3 class="card-title">Browsers</h3>
                <div v-if="!analytics.clicks_by_browser?.length" class="no-data">No data yet</div>
                <div v-else class="bar-list">
                    <div v-for="row in analytics.clicks_by_browser.slice(0,5)" :key="row.browser" class="bar-item">
                        <div class="bar-item__label">
                            <span>{{ row.browser || 'Unknown' }}</span>
                            <span class="bar-item__count">{{ row.total }}</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" :style="{ width: `${Math.round((row.total / maxDevice) * 100)}%`, background: '#A855F7' }" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Modal (Story 6.1) -->
        <div v-if="showReportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Link</h3>
                    
                    <div v-if="reportSubmitted" class="p-4 bg-green-100 text-green-800 rounded mb-4">
                        ✓ Report submitted. Thank you for helping keep our platform safe.
                    </div>
                    
                    <form v-else @submit.prevent="submitReport">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason</label>
                                <select v-model="reportForm.reason" class="block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <option v-for="opt in reasonOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Details (optional)</label>
                                <textarea v-model="reportForm.details" rows="3" class="block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="Additional information..."></textarea>
                            </div>
                            <div v-if="reportError" class="p-3 bg-red-100 text-red-800 rounded text-sm">{{ reportError }}</div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="showReportModal = false" class="px-4 py-2 border rounded text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit" :disabled="reportForm.processing" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50">
                                {{ reportForm.processing ? 'Submitting...' : 'Submit Report' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* ── Hero ─────────────────────────────────────── */
.link-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
}

.link-hero__left {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.link-hero__short {
    display: flex;
    align-items: center;
    gap: 10px;
}

.short-anchor {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: #22D3EE;
    text-decoration: none;
    letter-spacing: -0.01em;
}

.short-anchor:hover { text-decoration: underline; }

.copy-pill {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    background: rgba(34,211,238,0.1);
    border: 1px solid rgba(34,211,238,0.25);
    color: #22D3EE;
    cursor: pointer;
    transition: all 200ms;
}

.copy-pill:hover { background: rgba(34,211,238,0.2); }

.dest-anchor {
    font-size: 13px;
    color: #71717A;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 480px;
    transition: color 200ms;
}

.dest-anchor:hover { color: #A1A1AA; }

.link-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}

.meta-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
}

.meta-badge--active { color: #22C55E; background: rgba(34,197,94,0.1); }
.meta-badge--inactive { color: #EF4444; background: rgba(239,68,68,0.1); }
.meta-badge--tag { color: #F59E0B; background: rgba(245,158,11,0.1); }

.meta-created {
    font-size: 12px;
    color: #52525B;
}

.link-hero__actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}

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

.mini-stat__label {
    font-size: 11px;
    color: #52525B;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* ── Analytics grid ──────────────────────────── */
.analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.analytics-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 20px;
}

.card-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #A1A1AA;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 16px;
}

.no-data {
    font-size: 13px;
    color: #3F3F46;
    padding: 20px 0;
    text-align: center;
}

/* ── Bar list ─────────────────────────────────── */
.bar-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.bar-item__label {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: #A1A1AA;
    margin-bottom: 5px;
}

.bar-item__count {
    font-weight: 600;
    color: #FAFAFA;
}

.bar-track {
    height: 4px;
    background: rgba(255,255,255,0.06);
    border-radius: 2px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 600ms ease;
}

/* ── Referrer list ───────────────────────────── */
.referrer-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.referrer-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: rgba(255,255,255,0.02);
    border-radius: 6px;
}

.referrer-domain {
    font-size: 13px;
    color: #A1A1AA;
}

.referrer-count {
    font-size: 13px;
    font-weight: 600;
    color: #FAFAFA;
}

/* ── Buttons ──────────────────────────────────── */
.btn-ghost {
    display: inline-flex;
    align-items: center;
    padding: 8px 18px;
    background: transparent;
    color: #71717A;
    font-size: 13px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.08);
    text-decoration: none;
    transition: all 200ms;
}

.btn-ghost:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }

.btn-danger {
    display: inline-flex;
    align-items: center;
    padding: 8px 18px;
    background: transparent;
    color: #EF4444;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    border: 1px solid rgba(239,68,68,0.25);
    cursor: pointer;
    transition: all 200ms;
}

.btn-danger:hover { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.5); }

/* ── Share row (3.5, 3.4, 3.6) ───────────────── */
.share-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
    padding: 12px 20px;
    margin-bottom: 12px;
}
.share-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.share-label {
    font-size: 10px;
    color: #52525B;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-right: 4px;
}
.share-btn {
    font-size: 14px;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 700;
    transition: opacity 200ms;
    background: rgba(255,255,255,0.05);
    color: #A1A1AA;
    border: 1px solid rgba(255,255,255,0.06);
}
.share-btn:hover { opacity: 0.7; }
.qr-btn {
    font-size: 11px; font-weight: 600; padding: 5px 12px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 6px; color: #A1A1AA; cursor: pointer; text-decoration: none;
    transition: all 200ms;
}
.qr-btn:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }

/* ── Embed panel ──────────────────────────────── */
.embed-panel {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #0D0D0D;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 12px;
}
.embed-code {
    flex: 1;
    font-size: 12px;
    color: #71717A;
    font-family: 'JetBrains Mono', monospace;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Period filter bar ────────────────────────── */
.period-bar {
    display: flex;
    gap: 6px;
    margin-bottom: 16px;
}
.period-btn {
    font-size: 12px; font-weight: 500; padding: 6px 14px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.06);
    background: transparent; color: #52525B;
    cursor: pointer; transition: all 200ms;
}
.period-btn:hover { color: #A1A1AA; border-color: rgba(255,255,255,0.12); }
.period-btn--active {
    background: rgba(34,211,238,0.08);
    border-color: rgba(34,211,238,0.25);
    color: #22D3EE;
}

/* ── Country flag ─────────────────────────────── */
.country-flag { font-size: 15px; margin-right: 4px; }

@media (max-width: 768px) {
    .analytics-grid { grid-template-columns: 1fr; }
    .stat-row { grid-template-columns: 1fr 1fr; }
    .link-hero { flex-direction: column; }
    .share-row { flex-direction: column; align-items: flex-start; }
}
</style>
