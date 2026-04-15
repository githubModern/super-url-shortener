<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    links: {
        type: Object,
        required: true,
    },
});

const copiedId = ref(null);

const copyShortUrl = async (shortCode) => {
    const url = `${window.location.origin}/${shortCode}`;
    await navigator.clipboard.writeText(url);
    copiedId.value = shortCode;
    setTimeout(() => { copiedId.value = null; }, 2000);
};

const deleteLink = (id) => {
    if (!confirm('Delete this link? This cannot be undone.')) return;
    router.delete(route('links.destroy', id));
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head title="My Links — Editorial" />

    <AuthenticatedLayout>
        <template #header>My Links</template>

        <!-- Editorial Header -->
        <div class="editorial-header">
            <div class="issue-meta">
                <span class="roman-num">I.</span>
                <span class="meta-label">Archive</span>
            </div>
            <p class="page-sub">{{ links.total ?? 0 }} entries catalogued</p>
            <Link :href="route('links.create')" class="btn-primary">
                New Entry
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="links.data?.length === 0" class="empty">
            <div class="empty__roman">I.</div>
            <h2 class="empty__title">No entries yet</h2>
            <p class="empty__sub">Begin your collection by creating the first short link.</p>
            <Link :href="route('links.create')" class="btn-primary">Create Entry</Link>
        </div>

        <!-- Editorial Table -->
        <div v-else class="editorial-table">
            <div class="table-head">
                <span>Destination</span>
                <span>Short URL</span>
                <span>Clicks</span>
                <span>Status</span>
                <span>Created</span>
                <span>Actions</span>
            </div>

            <div v-for="link in links.data" :key="link.id" class="table-row">
                <!-- Destination -->
                <div class="cell-dest">
                    <a :href="link.destination_url" target="_blank" rel="noopener" class="dest-url">
                        {{ link.destination_url }}
                    </a>
                    <span v-if="link.campaign_tag" class="tag">{{ link.campaign_tag }}</span>
                </div>

                <!-- Short URL -->
                <div class="cell-short">
                    <a :href="`/${link.short_code}`" target="_blank" class="short-url">
                        /{{ link.short_code }}
                    </a>
                    <button @click="copyShortUrl(link.short_code)" class="copy-btn">
                        {{ copiedId === link.short_code ? '✓ Copied' : 'Copy' }}
                    </button>
                </div>

                <!-- Clicks -->
                <span class="cell-clicks">{{ (link.clicks_count ?? 0).toLocaleString() }}</span>

                <!-- Status -->
                <span class="cell-status" :class="link.is_active ? 'status--active' : 'status--inactive'">
                    {{ link.is_active ? 'Active' : 'Inactive' }}
                </span>

                <!-- Date -->
                <span class="cell-date">{{ formatDate(link.created_at) }}</span>

                <!-- Actions -->
                <div class="cell-actions">
                    <Link :href="route('links.show', link.id)" class="action-btn" title="Analytics">Stats</Link>
                    <Link :href="route('links.edit', link.id)" class="action-btn" title="Edit">Edit</Link>
                    <button @click="deleteLink(link.id)" class="action-btn action-btn--danger" title="Delete">Del</button>
                </div>
            </div>
        </div>

        <!-- Editorial Pagination -->
        <div v-if="links.last_page > 1" class="pagination">
            <Link
                v-if="links.prev_page_url"
                :href="links.prev_page_url"
                class="page-btn"
            >← Previous</Link>
            <span class="page-info">Page {{ links.current_page }} of {{ links.last_page }}</span>
            <Link
                v-if="links.next_page_url"
                :href="links.next_page_url"
                class="page-btn"
            >Next →</Link>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

/* ── Editorial Header ────────────────────────────── */
.editorial-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.issue-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}

.roman-num {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #e74c3c;
    letter-spacing: 2px;
}

.meta-label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #888;
}

.page-sub {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    font-style: italic;
    color: #888;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 12px 28px;
    background: #e74c3c;
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 3px;
    text-transform: uppercase;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #c0392b;
}

/* ── Editorial Table ─────────────────────────────── */
.editorial-table {
    background: #fff;
    border: 1px solid #ddd;
}

.table-head {
    display: grid;
    grid-template-columns: 2.5fr 1.2fr 80px 80px 110px 120px;
    padding: 16px 24px;
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #888;
    background: #fafafa;
    border-bottom: 2px solid #1a1a1a;
}

.table-row {
    display: grid;
    grid-template-columns: 2.5fr 1.2fr 80px 80px 110px 120px;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #eee;
    transition: background 0.2s;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row:hover {
    background: #f0f0f0;
}

/* ── Cells ───────────────────────────────────────── */
.cell-dest {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
    padding-right: 16px;
}

.dest-url {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    color: #666;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.2s;
}

.dest-url:hover {
    color: #1a1a1a;
    text-decoration: underline;
}

.tag {
    display: inline-block;
    font-family: 'Oswald', sans-serif;
    font-size: 9px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #d4af37;
    border: 1px solid #d4af37;
    padding: 2px 8px;
    width: fit-content;
}

.cell-short {
    display: flex;
    align-items: center;
    gap: 12px;
}

.short-url {
    font-family: 'Oswald', sans-serif;
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 1px;
    color: #e74c3c;
    text-decoration: none;
}

.short-url:hover {
    text-decoration: underline;
}

.copy-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 9px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #888;
    background: none;
    border: 1px solid #ddd;
    padding: 4px 10px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}

.copy-btn:hover {
    color: #1a1a1a;
    border-color: #1a1a1a;
}

.cell-clicks {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 1px;
    color: #1a1a1a;
}

.cell-status {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.status--active {
    color: #27ae60;
}

.status--inactive {
    color: #e74c3c;
}

.cell-date {
    font-family: 'Crimson Pro', serif;
    font-size: 13px;
    font-style: italic;
    color: #888;
}

.cell-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 9px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #888;
    text-decoration: none;
    padding: 6px 12px;
    border: 1px solid #ddd;
    background: none;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.action-btn:hover {
    color: #1a1a1a;
    border-color: #1a1a1a;
}

.action-btn--danger:hover {
    color: #e74c3c;
    border-color: #e74c3c;
}

/* ── Empty State ──────────────────────────────────── */
.empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100px 20px;
    text-align: center;
    gap: 16px;
    border: 1px solid #ddd;
    background: #fff;
}

.empty__roman {
    font-family: 'Oswald', sans-serif;
    font-size: 48px;
    font-weight: 700;
    color: #e74c3c;
    letter-spacing: 4px;
}

.empty__title {
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    font-weight: 500;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #1a1a1a;
}

.empty__sub {
    font-family: 'Crimson Pro', serif;
    font-size: 16px;
    font-style: italic;
    color: #888;
    margin-bottom: 8px;
}

/* ── Pagination ──────────────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.page-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #888;
    text-decoration: none;
    padding: 10px 20px;
    border: 1px solid #ddd;
    transition: all 0.2s;
}

.page-btn:hover {
    color: #1a1a1a;
    border-color: #1a1a1a;
}

.page-info {
    font-family: 'Crimson Pro', serif;
    font-size: 14px;
    font-style: italic;
    color: #888;
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 900px) {
    .editorial-header {
        flex-wrap: wrap;
        gap: 16px;
    }

    .table-head,
    .table-row {
        grid-template-columns: 2fr 1fr 70px 80px;
    }

    .cell-date,
    .table-head span:nth-child(5) {
        display: none;
    }
}

@media (max-width: 640px) {
    .table-head,
    .table-row {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .cell-clicks,
    .cell-status,
    .table-head span:nth-child(3),
    .table-head span:nth-child(4) {
        display: none;
    }

    .cell-actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
        padding-top: 8px;
        border-top: 1px solid #eee;
    }
}
</style>
