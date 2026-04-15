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

const statItems = computed(() => [
    { num: '01', label: 'Total Links', value: props.stats.total_links },
    { num: '02', label: 'Total Clicks', value: props.stats.total_clicks },
    { num: '03', label: 'Clicks Today', value: props.stats.clicks_today },
    { num: '04', label: 'Active Links', value: props.stats.active_links },
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

        <div class="dashboard-editorial">
            <!-- Editorial Masthead -->
            <div class="dashboard-masthead">
                <span class="masthead-label">Vol. 01 — Control Panel</span>
                <h1 class="masthead-title">Overview</h1>
            </div>

            <!-- Stat Cards - Editorial Specifications -->
            <div class="specs-section">
                <h3 class="section-heading">Specifications</h3>
                <div class="specs-grid">
                    <div
                        v-for="item in statItems"
                        :key="item.num"
                        class="spec-card"
                    >
                        <span class="spec-number">{{ item.num }}</span>
                        <span class="spec-label-text">{{ item.label }}</span>
                        <span class="spec-value">{{ item.value.toLocaleString() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Create - Editorial Form -->
            <div class="editorial-form-section">
                <label class="form-label">Shorten a URL</label>
                <div class="editorial-input-wrap">
                    <input
                        type="url"
                        placeholder="https://your-long-url.com/paste-here"
                        class="editorial-input"
                        readonly
                        @focus="$inertia.visit(route('links.create'))"
                    />
                    <Link :href="route('links.create')" class="editorial-submit">
                        Create
                    </Link>
                </div>
            </div>

            <!-- Recent Links - Typography Forward Table -->
            <div class="links-section">
                <div class="links-header">
                    <h3 class="section-heading">Recent Links</h3>
                    <Link :href="route('links.index')" class="view-all-link">View all archive →</Link>
                </div>

                <div v-if="recentLinks.length === 0" class="empty-state">
                    <p class="empty-text">No entries recorded. Create your first short link above.</p>
                </div>

                <div v-else class="editorial-table">
                    <div class="table-head">
                        <span class="col-dest">Destination</span>
                        <span class="col-short">Short URL</span>
                        <span class="col-clicks">Clicks</span>
                        <span class="col-date">Created</span>
                        <span class="col-actions"></span>
                    </div>
                    <div
                        v-for="link in recentLinks"
                        :key="link.id"
                        class="table-row"
                    >
                        <span class="cell-dest">{{ link.destination_url }}</span>
                        <a
                            :href="`/${link.short_code}`"
                            target="_blank"
                            class="cell-short"
                        >{{ link.short_code }}</a>
                        <span class="cell-clicks">{{ link.clicks_count }}</span>
                        <span class="cell-date">{{ formatDate(link.created_at) }}</span>
                        <div class="cell-actions">
                            <Link :href="route('links.show', link.id)" class="table-action">Stats</Link>
                            <Link :href="route('links.edit', link.id)" class="table-action">Edit</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

/* ── EDITORIAL DASHBOARD ─────────────────────────── */

.dashboard-editorial {
    font-family: 'Crimson Pro', serif;
    color: #1a1a1a;
}

/* ── Masthead ──────────────────────────────────── */
.dashboard-masthead {
    margin-bottom: 48px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 32px;
}

.masthead-label {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 16px;
}

.masthead-title {
    font-family: 'Oswald', sans-serif;
    font-size: 48px;
    font-weight: 700;
    line-height: 1;
    letter-spacing: -1px;
    color: #1a1a1a;
    margin: 0;
}

/* ── Section Heading ───────────────────────────── */
.section-heading {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #666;
    margin: 0 0 24px 0;
}

/* ── Specifications Grid ──────────────────────── */
.specs-section {
    margin-bottom: 48px;
}

.specs-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    background: #1a1a1a;
}

.spec-card {
    display: flex;
    flex-direction: column;
    padding: 32px 28px;
    border-right: 1px solid #2a2a2a;
    position: relative;
}

.spec-card:last-child {
    border-right: none;
}

.spec-number {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    color: #555;
    letter-spacing: 2px;
    margin-bottom: 12px;
}

.spec-label-text {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #888;
    margin-bottom: 8px;
}

.spec-value {
    font-family: 'Oswald', sans-serif;
    font-size: 36px;
    font-weight: 700;
    color: #d4af37;
    letter-spacing: 1px;
    line-height: 1;
}

/* ── Editorial Form ─────────────────────────────── */
.editorial-form-section {
    margin-bottom: 48px;
    padding-bottom: 48px;
    border-bottom: 1px solid #ddd;
}

.form-label {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #999;
    margin-bottom: 16px;
}

.editorial-input-wrap {
    display: flex;
    border: 1px solid rgba(0,0,0,0.2);
    background: #fff;
    padding: 4px;
}

.editorial-input {
    flex: 1;
    border: none;
    padding: 20px 24px;
    font-family: 'Crimson Pro', serif;
    font-size: 18px;
    outline: none;
    color: #1a1a1a;
    background: transparent;
    cursor: pointer;
}

.editorial-input::placeholder {
    color: #bbb;
    font-style: italic;
}

.editorial-submit {
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 20px 40px;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.3s;
    display: inline-flex;
    align-items: center;
}

.editorial-submit:hover {
    background: #c0392b;
}

/* ── Links Section ─────────────────────────────── */
.links-section {
    margin-bottom: 32px;
}

.links-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 24px;
}

.view-all-link {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
    text-decoration: none;
    transition: color 0.2s;
}

.view-all-link:hover {
    color: #e74c3c;
}

/* ── Editorial Table ───────────────────────────── */
.editorial-table {
    display: flex;
    flex-direction: column;
}

.table-head {
    display: grid;
    grid-template-columns: 3fr 1.5fr 100px 120px 160px;
    padding: 12px 0;
    border-bottom: 1px solid #1a1a1a;
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #666;
}

.table-row {
    display: grid;
    grid-template-columns: 3fr 1.5fr 100px 120px 160px;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #e0e0e0;
    transition: background 0.2s;
}

.table-row:hover {
    background: #f5f5f5;
}

.cell-dest {
    font-size: 15px;
    color: #444;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding-right: 16px;
    font-style: italic;
}

.cell-short {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    color: #e74c3c;
    text-decoration: none;
    letter-spacing: 1px;
    font-weight: 500;
}

.cell-short:hover {
    text-decoration: underline;
}

.cell-clicks {
    font-family: 'Oswald', sans-serif;
    font-size: 16px;
    font-weight: 500;
    color: #1a1a1a;
    letter-spacing: 1px;
}

.cell-date {
    font-size: 14px;
    color: #888;
}

.cell-actions {
    display: flex;
    gap: 12px;
}

.table-action {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #666;
    text-decoration: none;
    padding: 6px 12px;
    border: 1px solid #ddd;
    transition: all 0.2s;
}

.table-action:hover {
    color: #1a1a1a;
    border-color: #1a1a1a;
}

/* ── Empty State ───────────────────────────────── */
.empty-state {
    padding: 60px 0;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
}

.empty-text {
    font-size: 18px;
    color: #999;
    font-style: italic;
    margin: 0;
}

/* ── Responsive ────────────────────────────────── */
@media (max-width: 1024px) {
    .specs-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .spec-card:nth-child(2) {
        border-right: none;
    }

    .spec-card:nth-child(1),
    .spec-card:nth-child(2) {
        border-bottom: 1px solid #2a2a2a;
    }

    .table-head,
    .table-row {
        grid-template-columns: 2fr 1fr 80px 100px 120px;
    }
}

@media (max-width: 768px) {

    .masthead-title {
        font-size: 36px;
    }

    .specs-grid {
        grid-template-columns: 1fr;
    }

    .spec-card {
        border-right: none;
        border-bottom: 1px solid #2a2a2a;
    }

    .spec-card:last-child {
        border-bottom: none;
    }

    .editorial-input-wrap {
        flex-direction: column;
    }

    .editorial-submit {
        justify-content: center;
    }

    .table-head {
        display: none;
    }

    .table-row {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 20px 0;
    }

    .cell-dest {
        grid-column: 1 / -1;
        padding-right: 0;
        margin-bottom: 8px;
    }

    .cell-clicks {
        text-align: right;
    }

    .cell-actions {
        justify-content: flex-end;
    }
}
</style>
