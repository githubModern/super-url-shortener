<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_links: 0,
            total_clicks: 0,
            clicks_today: 0,
            active_links: 0,
        }),
    },
    recentLinks: {
        type: Array,
        default: () => [],
    },
});

const statCards = computed(() => [
    {
        label: 'Total Links',
        value: props.stats.total_links,
        icon: 'M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71',
        accent: '#22D3EE',
    },
    {
        label: 'Total Clicks',
        value: props.stats.total_clicks,
        icon: 'M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122',
        accent: '#22C55E',
    },
    {
        label: 'Clicks Today',
        value: props.stats.clicks_today,
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        accent: '#F59E0B',
    },
    {
        label: 'Active Links',
        value: props.stats.active_links,
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        accent: '#A855F7',
    },
]);

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>Dashboard</template>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="stat-card"
                :style="`--accent: ${card.accent}`"
            >
                <div class="stat-card__icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="stat-card__icon">
                        <path :d="card.icon" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="stat-card__body">
                    <span class="stat-card__value">{{ card.value.toLocaleString() }}</span>
                    <span class="stat-card__label">{{ card.label }}</span>
                </div>
                <div class="stat-card__bar" />
            </div>
        </div>

        <!-- Quick Create -->
        <div class="quick-create">
            <h2 class="section-title">Shorten a URL</h2>
            <div class="quick-create__row">
                <form @submit.prevent="$inertia.visit(route('links.create'))" class="quick-create__form">
                    <input
                        type="url"
                        placeholder="https://your-long-url.com/paste-here"
                        class="quick-create__input"
                        readonly
                        @focus="$inertia.visit(route('links.create'))"
                    />
                    <Link :href="route('links.create')" class="btn-primary">
                        Shorten
                    </Link>
                </form>
            </div>
        </div>

        <!-- Recent Links -->
        <div class="recent-section">
            <div class="section-header">
                <h2 class="section-title">Recent Links</h2>
                <Link :href="route('links.index')" class="section-link">View all →</Link>
            </div>

            <div v-if="recentLinks.length === 0" class="empty-state">
                <p>No links yet. Create your first short link above.</p>
            </div>

            <div v-else class="links-table">
                <div class="links-table__head">
                    <span>Destination</span>
                    <span>Short URL</span>
                    <span>Clicks</span>
                    <span>Created</span>
                    <span></span>
                </div>
                <div
                    v-for="link in recentLinks"
                    :key="link.id"
                    class="links-table__row"
                >
                    <span class="links-table__dest">{{ link.destination_url }}</span>
                    <a
                        :href="`/${link.short_code}`"
                        target="_blank"
                        class="links-table__short"
                    >{{ link.short_code }}</a>
                    <span class="links-table__clicks">{{ link.clicks_count }}</span>
                    <span class="links-table__date">{{ formatDate(link.created_at) }}</span>
                    <div class="links-table__actions">
                        <Link :href="route('links.show', link.id)" class="action-btn">Stats</Link>
                        <Link :href="route('links.edit', link.id)" class="action-btn">Edit</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* ── Stat Cards ─────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.stat-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    overflow: hidden;
    transition: border-color 200ms;
}

.stat-card:hover {
    border-color: var(--accent, #22D3EE);
}

.stat-card__bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--accent, #22D3EE);
    opacity: 0.4;
}

.stat-card__icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: rgba(255,255,255,0.04);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--accent, #22D3EE);
}

.stat-card__icon {
    width: 20px;
    height: 20px;
}

.stat-card__body {
    display: flex;
    flex-direction: column;
}

.stat-card__value {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: #FAFAFA;
    line-height: 1;
    letter-spacing: -0.02em;
}

.stat-card__label {
    font-size: 12px;
    color: #71717A;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* ── Quick Create ───────────────────────────────── */
.quick-create {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 32px;
}

.quick-create__form {
    display: flex;
    gap: 12px;
}

.quick-create__input {
    flex: 1;
    background: #0A0A0A;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 14px;
    color: #FAFAFA;
    outline: none;
    cursor: pointer;
    transition: border-color 200ms;
}

.quick-create__input:hover,
.quick-create__input:focus {
    border-color: #22D3EE;
}

/* ── Section ────────────────────────────────────── */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.section-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: #FAFAFA;
    margin-bottom: 16px;
}

.section-header .section-title { margin-bottom: 0; }

.section-link {
    font-size: 13px;
    color: #22D3EE;
    text-decoration: none;
    transition: opacity 200ms;
}

.section-link:hover { opacity: 0.7; }

/* ── Links Table ────────────────────────────────── */
.recent-section {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 24px;
}

.links-table {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.links-table__head {
    display: grid;
    grid-template-columns: 2fr 1fr 80px 90px 140px;
    padding: 8px 12px;
    font-size: 11px;
    font-weight: 600;
    color: #52525B;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    margin-bottom: 4px;
}

.links-table__row {
    display: grid;
    grid-template-columns: 2fr 1fr 80px 90px 140px;
    align-items: center;
    padding: 12px 12px;
    border-radius: 8px;
    transition: background 200ms;
}

.links-table__row:hover {
    background: rgba(255,255,255,0.03);
}

.links-table__dest {
    font-size: 13px;
    color: #A1A1AA;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-right: 12px;
}

.links-table__short {
    font-size: 13px;
    color: #22D3EE;
    text-decoration: none;
    font-weight: 500;
}

.links-table__short:hover {
    text-decoration: underline;
}

.links-table__clicks {
    font-size: 13px;
    font-weight: 600;
    color: #FAFAFA;
}

.links-table__date {
    font-size: 12px;
    color: #52525B;
}

.links-table__actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    font-size: 12px;
    color: #71717A;
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.08);
    transition: all 200ms;
}

.action-btn:hover {
    color: #FAFAFA;
    border-color: rgba(255,255,255,0.2);
}

/* ── Btn Primary ────────────────────────────────── */
.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: #22D3EE;
    color: #0A0A0A;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    white-space: nowrap;
    transition: opacity 200ms;
}

.btn-primary:hover { opacity: 0.85; }

/* ── Empty state ────────────────────────────────── */
.empty-state {
    padding: 40px 0;
    text-align: center;
    color: #52525B;
    font-size: 14px;
}

@media (max-width: 768px) {
    .links-table__head,
    .links-table__row {
        grid-template-columns: 1fr 1fr;
    }

    .links-table__dest,
    .links-table__clicks,
    .links-table__date { display: none; }
}
</style>
