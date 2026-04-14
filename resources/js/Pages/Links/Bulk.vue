<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const raw     = ref('');
const results = ref([]);
const loading = ref(false);
const error   = ref(null);

const urls = computed(() =>
    raw.value.split('\n').map(u => u.trim()).filter(Boolean)
);

const successCount = computed(() => results.value.filter(r => r.status === 'success').length);
const errorCount   = computed(() => results.value.filter(r => r.status === 'error').length);

const submit = async () => {
    if (!urls.value.length) return;
    error.value   = null;
    results.value = [];
    loading.value = true;

    try {
        const res = await axios.post(route('links.bulk.store'), { urls: urls.value });
        results.value = res.data.results;
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Something went wrong.';
    } finally {
        loading.value = false;
    }
};

const exportCsv = async () => {
    const res = await axios.post(route('links.bulk.export'), { results: results.value }, {
        responseType: 'blob',
    });
    const url  = URL.createObjectURL(res.data);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `bulk-links-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
};

const handleFile = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => { raw.value = ev.target.result; };
    reader.readAsText(file);
};
</script>

<template>
    <Head title="Bulk URL Shortener" />

    <AuthenticatedLayout>
        <template #header>Bulk URL Shortener</template>

        <div class="bulk-layout">
            <!-- Input Panel -->
            <div class="panel">
                <div class="panel__header">
                    <h3 class="panel__title">Paste URLs</h3>
                    <label class="upload-btn">
                        Upload CSV
                        <input type="file" accept=".csv,.txt" class="sr-only" @change="handleFile" />
                    </label>
                </div>

                <textarea
                    v-model="raw"
                    class="url-textarea"
                    placeholder="Paste one URL per line (max 500)&#10;https://example.com/very-long-url-1&#10;https://example.com/very-long-url-2"
                    rows="16"
                />

                <div class="panel__footer">
                    <span class="count-badge">{{ urls.length }} / 500 URLs</span>
                    <button
                        @click="submit"
                        class="btn-primary"
                        :disabled="loading || !urls.length || urls.length > 500"
                    >
                        <span v-if="loading" class="spinner" />
                        <span v-else>Shorten All →</span>
                    </button>
                </div>

                <p v-if="error" class="field-error">{{ error }}</p>
            </div>

            <!-- Results Panel -->
            <div v-if="results.length" class="panel">
                <div class="panel__header">
                    <h3 class="panel__title">
                        Results
                        <span class="badge badge--green">{{ successCount }} ok</span>
                        <span v-if="errorCount" class="badge badge--red">{{ errorCount }} failed</span>
                    </h3>
                    <button @click="exportCsv" class="btn-ghost">Export CSV</button>
                </div>

                <div class="results-table">
                    <div class="results-head">
                        <span>Original URL</span>
                        <span>Short URL</span>
                        <span>Status</span>
                    </div>
                    <div
                        v-for="(row, i) in results"
                        :key="i"
                        class="results-row"
                        :class="row.status === 'error' ? 'results-row--error' : ''"
                    >
                        <span class="cell-url cell-url--original" :title="row.original_url">{{ row.original_url }}</span>
                        <a v-if="row.short_url" :href="row.short_url" target="_blank" class="cell-url cell-url--short">{{ row.short_url }}</a>
                        <span v-else class="cell-url cell-url--err">{{ row.error }}</span>
                        <span :class="row.status === 'success' ? 'status-ok' : 'status-err'">
                            {{ row.status === 'success' ? '✓' : '✗' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.bulk-layout { display: flex; flex-direction: column; gap: 20px; }

.panel {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
}

.panel__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.panel__title {
    font-size: 14px; font-weight: 600; color: #FAFAFA; margin: 0;
    display: flex; align-items: center; gap: 8px;
}

.panel__footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 20px;
    border-top: 1px solid rgba(255,255,255,0.05);
}

.url-textarea {
    width: 100%; background: transparent; border: none; outline: none;
    padding: 16px 20px; font-size: 13px; color: #A1A1AA; font-family: 'JetBrains Mono', monospace;
    line-height: 1.6; resize: vertical; min-height: 240px;
}
.url-textarea::placeholder { color: #3F3F46; }

.count-badge { font-size: 12px; color: #52525B; }

.upload-btn {
    font-size: 12px; color: #71717A;
    border: 1px solid rgba(255,255,255,0.08); border-radius: 6px;
    padding: 5px 12px; cursor: pointer; transition: all 200ms;
}
.upload-btn:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }

.btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 20px; background: #22D3EE; color: #000;
    font-size: 13px; font-weight: 700; border-radius: 8px; border: none;
    cursor: pointer; transition: background 200ms;
}
.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-primary:not(:disabled):hover { background: #06B6D4; }

.btn-ghost {
    font-size: 12px; color: #71717A;
    border: 1px solid rgba(255,255,255,0.08); border-radius: 6px;
    padding: 5px 12px; background: none; cursor: pointer; transition: all 200ms;
}
.btn-ghost:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }

.badge {
    font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 999px; text-transform: uppercase; letter-spacing: 0.05em;
}
.badge--green { background: rgba(34,197,94,0.1); color: #22C55E; }
.badge--red   { background: rgba(239,68,68,0.1);  color: #EF4444; }

.field-error { font-size: 12px; color: #EF4444; padding: 8px 20px; }

/* Results table */
.results-table { max-height: 400px; overflow-y: auto; }
.results-head {
    display: grid; grid-template-columns: 1fr 200px 60px;
    padding: 8px 20px; font-size: 10px; font-weight: 600; color: #52525B;
    text-transform: uppercase; letter-spacing: 0.06em;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid rgba(255,255,255,0.04);
    position: sticky; top: 0;
}
.results-row {
    display: grid; grid-template-columns: 1fr 200px 60px;
    align-items: center; padding: 10px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    transition: background 150ms;
}
.results-row:hover { background: rgba(255,255,255,0.02); }
.results-row--error { background: rgba(239,68,68,0.02); }

.cell-url { font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cell-url--original { color: #52525B; }
.cell-url--short { color: #22D3EE; text-decoration: none; }
.cell-url--short:hover { text-decoration: underline; }
.cell-url--err { color: #EF4444; font-size: 11px; }

.status-ok  { color: #22C55E; font-weight: 700; font-size: 14px; text-align: center; }
.status-err { color: #EF4444; font-weight: 700; font-size: 14px; text-align: center; }

.spinner {
    width: 14px; height: 14px; border: 2px solid rgba(0,0,0,0.2);
    border-top-color: #000; border-radius: 50%;
    animation: spin 0.6s linear infinite; display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
