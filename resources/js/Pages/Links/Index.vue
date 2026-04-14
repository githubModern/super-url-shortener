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
    <Head title="My Links" />

    <AuthenticatedLayout>
        <template #header>My Links</template>

        <!-- Header Row -->
        <div class="page-header">
            <p class="page-sub">{{ links.total ?? 0 }} links total</p>
            <Link :href="route('links.create')" class="btn-primary">
                + New Link
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="links.data?.length === 0" class="empty">
            <div class="empty__icon">⚡</div>
            <h2 class="empty__title">No links yet</h2>
            <p class="empty__sub">Create your first short link to get started.</p>
            <Link :href="route('links.create')" class="btn-primary">Create Short Link</Link>
        </div>

        <!-- Links Table -->
        <div v-else class="table-card">
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

        <!-- Pagination -->
        <div v-if="links.last_page > 1" class="pagination">
            <Link
                v-if="links.prev_page_url"
                :href="links.prev_page_url"
                class="page-btn"
            >← Prev</Link>
            <span class="page-info">{{ links.current_page }} / {{ links.last_page }}</span>
            <Link
                v-if="links.next_page_url"
                :href="links.next_page_url"
                class="page-btn"
            >Next →</Link>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.page-sub {
    font-size: 13px;
    color: #52525B;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 9px 18px;
    background: #22D3EE;
    color: #0A0A0A;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: opacity 200ms;
}

.btn-primary:hover { opacity: 0.85; }

/* ── Table ──────────────────────────────────────── */
.table-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
}

.table-head {
    display: grid;
    grid-template-columns: 2.5fr 1.2fr 80px 80px 110px 120px;
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
    grid-template-columns: 2.5fr 1.2fr 80px 80px 110px 120px;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 200ms;
}

.table-row:last-child { border-bottom: none; }

.table-row:hover {
    background: rgba(255,255,255,0.02);
}

/* ── Cells ───────────────────────────────────────── */
.cell-dest {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    padding-right: 12px;
}

.dest-url {
    font-size: 13px;
    color: #A1A1AA;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 200ms;
}

.dest-url:hover { color: #FAFAFA; }

.tag {
    display: inline-block;
    font-size: 10px;
    color: #F59E0B;
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.2);
    border-radius: 4px;
    padding: 1px 6px;
    width: fit-content;
}

.cell-short {
    display: flex;
    align-items: center;
    gap: 8px;
}

.short-url {
    font-size: 13px;
    color: #22D3EE;
    text-decoration: none;
    font-weight: 500;
}

.short-url:hover { text-decoration: underline; }

.copy-btn {
    font-size: 11px;
    color: #52525B;
    background: none;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 4px;
    padding: 2px 8px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 200ms;
}

.copy-btn:hover {
    color: #FAFAFA;
    border-color: rgba(255,255,255,0.2);
}

.cell-clicks {
    font-size: 13px;
    font-weight: 600;
    color: #FAFAFA;
}

.cell-status { font-size: 12px; font-weight: 500; }

.status--active { color: #22C55E; }
.status--inactive { color: #EF4444; }

.cell-date {
    font-size: 12px;
    color: #52525B;
}

.cell-actions {
    display: flex;
    gap: 6px;
}

.action-btn {
    font-size: 11px;
    color: #71717A;
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.08);
    background: none;
    cursor: pointer;
    transition: all 200ms;
    white-space: nowrap;
}

.action-btn:hover {
    color: #FAFAFA;
    border-color: rgba(255,255,255,0.2);
}

.action-btn--danger:hover {
    color: #EF4444;
    border-color: rgba(239,68,68,0.3);
}

/* ── Empty ───────────────────────────────────────── */
.empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    text-align: center;
    gap: 12px;
}

.empty__icon { font-size: 48px; opacity: 0.3; }

.empty__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 20px;
    font-weight: 600;
    color: #FAFAFA;
}

.empty__sub {
    font-size: 14px;
    color: #52525B;
    margin-bottom: 8px;
}

/* ── Pagination ─────────────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 24px;
}

.page-btn {
    font-size: 13px;
    color: #71717A;
    text-decoration: none;
    padding: 7px 16px;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    transition: all 200ms;
}

.page-btn:hover {
    color: #FAFAFA;
    border-color: rgba(255,255,255,0.2);
}

.page-info {
    font-size: 13px;
    color: #52525B;
}

@media (max-width: 900px) {
    .table-head,
    .table-row {
        grid-template-columns: 2fr 1fr 70px 80px;
    }

    .cell-date,
    .table-head span:nth-child(5) { display: none; }
}
</style>
