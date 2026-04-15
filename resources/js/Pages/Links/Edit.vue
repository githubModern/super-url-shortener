<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    link: { type: Object, required: true },
    ads: { type: Array, default: () => [] },
});

const form = useForm({
    destination_url: props.link.destination_url ?? '',
    campaign_tag: props.link.campaign_tag ?? '',
    og_title: props.link.og_title ?? '',
    og_description: props.link.og_description ?? '',
    is_active: props.link.is_active ?? true,
    ad_override: props.link.ad_override ?? 'inherit',
    ad_id: props.link.ad_id ?? null,
});

const showAdvanced = ref(!!(props.link.og_title || props.link.og_description));

const submit = () => {
    form.patch(route('links.update', props.link.id));
};
</script>

<template>
    <Head :title="`Edit /${link.short_code} — Editorial`" />

    <AuthenticatedLayout>
        <template #header>Edit Entry</template>

        <div class="editorial-layout">
            <div class="editorial-form-section">
                <!-- Short code badge -->
                <div class="entry-badge">
                    <span class="badge-label">Entry Code</span>
                    <a :href="`/${link.short_code}`" target="_blank" class="badge-code">
                        /{{ link.short_code }}
                    </a>
                    <span v-if="link.custom_alias" class="badge-alias">custom alias</span>
                </div>

                <div class="form-header">
                    <span class="roman-num">III.</span>
                    <span class="section-label">Revision</span>
                </div>

                <form @submit.prevent="submit" class="editorial-form">
                    <!-- Destination URL -->
                    <div class="field">
                        <label class="field__label">Destination URL <span class="field__required">*</span></label>
                        <input
                            v-model="form.destination_url"
                            type="url"
                            placeholder="https://example.com/your-long-url"
                            class="field__input"
                            :class="{ 'field__input--error': form.errors.destination_url }"
                            required
                        />
                        <span v-if="form.errors.destination_url" class="field__error">{{ form.errors.destination_url }}</span>
                    </div>

                    <!-- Campaign Tag -->
                    <div class="field">
                        <label class="field__label">Campaign Tag <span class="field__optional">(optional)</span></label>
                        <input
                            v-model="form.campaign_tag"
                            type="text"
                            placeholder="summer-promo"
                            class="field__input"
                            maxlength="100"
                        />
                    </div>

                    <!-- Active toggle -->
                    <div class="field field--row">
                        <label class="field__label">Entry Status</label>
                        <label class="toggle">
                            <input type="checkbox" v-model="form.is_active" class="toggle__input" />
                            <span class="toggle__track">
                                <span class="toggle__thumb" />
                            </span>
                            <span class="toggle__label">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>

                    <!-- Ad Settings -->
                    <div class="field">
                        <label class="field__label">Ad Display</label>
                        <select v-model="form.ad_override" class="field__input field__select">
                            <option value="inherit">Inherit global settings</option>
                            <option value="disable">Disable ads for this link</option>
                            <option value="force">Force specific ad</option>
                        </select>
                    </div>
                    <div v-if="form.ad_override === 'force'" class="field">
                        <label class="field__label">Select Ad</label>
                        <select v-model="form.ad_id" class="field__input field__select">
                            <option :value="null">-- Select an ad --</option>
                            <option v-for="ad in ads" :key="ad.id" :value="ad.id">{{ ad.name }} ({{ ad.format }})</option>
                        </select>
                    </div>

                    <!-- Advanced OG toggle -->
                    <button type="button" class="toggle-advanced" @click="showAdvanced = !showAdvanced">
                        <span class="roman-num-small">{{ showAdvanced ? '▲' : '▼' }}</span>
                        <span>Advanced (OG / Social Preview)</span>
                    </button>

                    <div v-if="showAdvanced" class="advanced-fields">
                        <div class="field">
                            <label class="field__label">OG Title</label>
                            <input v-model="form.og_title" type="text" placeholder="Override link preview title" class="field__input" maxlength="255" />
                        </div>
                        <div class="field">
                            <label class="field__label">OG Description</label>
                            <textarea v-model="form.og_description" placeholder="Override link preview description" class="field__textarea" rows="3" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <Link :href="route('links.show', link.id)" class="btn-secondary">Cancel</Link>
                        <button type="submit" class="btn-primary" :disabled="form.processing || !form.isDirty">
                            <span v-if="form.processing">Saving…</span>
                            <span v-else>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@400;500;700&display=swap');

/* ── Editorial Layout ───────────────────────────── */
.editorial-layout {
    max-width: 600px;
}

.editorial-form-section {
    background: #fff;
    border: 1px solid #ddd;
    padding: 40px;
}

/* ── Entry Badge ────────────────────────────────── */
.entry-badge {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: #fafafa;
    border: 1px solid #e0e0e0;
    margin-bottom: 32px;
}

.badge-label {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #888;
}

.badge-code {
    font-family: 'Oswald', sans-serif;
    font-size: 16px;
    font-weight: 500;
    letter-spacing: 2px;
    color: #e74c3c;
    text-decoration: none;
}

.badge-code:hover {
    text-decoration: underline;
}

.badge-alias {
    font-family: 'Crimson Pro', serif;
    font-size: 12px;
    font-style: italic;
    color: #d4af37;
}

/* ── Form Header ────────────────────────────────── */
.form-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 2px solid #1a1a1a;
}

.roman-num {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: #e74c3c;
    letter-spacing: 2px;
}

.roman-num-small {
    font-family: 'Oswald', sans-serif;
    font-size: 10px;
    color: #888;
}

.section-label {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #1a1a1a;
}

.editorial-form {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

/* ── Fields ──────────────────────────────────────── */
.field {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.field--row {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.field__label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #888;
}

.field__required {
    color: #e74c3c;
    margin-left: 4px;
}

.field__optional {
    font-family: 'Crimson Pro', serif;
    font-size: 12px;
    font-style: italic;
    text-transform: none;
    letter-spacing: 0;
    color: #aaa;
    font-weight: 400;
}

.field__input {
    background: transparent;
    border: none;
    border-bottom: 1px solid #ddd;
    padding: 12px 0;
    font-family: 'Crimson Pro', serif;
    font-size: 16px;
    color: #1a1a1a;
    outline: none;
    transition: border-color 0.3s;
    width: 100%;
}

.field__input::placeholder {
    color: #bbb;
    font-style: italic;
}

.field__input:focus {
    border-bottom-color: #e74c3c;
}

.field__input--error {
    border-bottom-color: #e74c3c;
}

.field__select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0 center;
    padding-right: 20px;
}

.field__textarea {
    background: transparent;
    border: 1px solid #ddd;
    padding: 16px;
    font-family: 'Crimson Pro', serif;
    font-size: 16px;
    color: #1a1a1a;
    outline: none;
    transition: border-color 0.3s;
    resize: vertical;
    min-height: 100px;
}

.field__textarea:focus {
    border-color: #e74c3c;
}

.field__error {
    font-family: 'Crimson Pro', serif;
    font-size: 13px;
    font-style: italic;
    color: #e74c3c;
}

/* ── Toggle Switch ───────────────────────────────── */
.toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.toggle__input {
    display: none;
}

.toggle__track {
    width: 44px;
    height: 24px;
    background: #ddd;
    display: flex;
    align-items: center;
    padding: 2px;
    transition: background 0.3s;
    position: relative;
}

.toggle__input:checked + .toggle__track {
    background: #27ae60;
}

.toggle__thumb {
    width: 20px;
    height: 20px;
    background: #fff;
    transition: transform 0.3s;
}

.toggle__input:checked + .toggle__track .toggle__thumb {
    transform: translateX(20px);
}

.toggle__label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #888;
}

/* ── Advanced Toggle ────────────────────────────── */
.toggle-advanced {
    display: flex;
    align-items: center;
    gap: 12px;
    background: none;
    border: none;
    cursor: pointer;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #888;
    text-align: left;
    padding: 0;
    transition: color 0.3s;
}

.toggle-advanced:hover {
    color: #1a1a1a;
}

.advanced-fields {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 24px;
    background: #fafafa;
    border-left: 3px solid #d4af37;
}

/* ── Actions ───────────────────────────────────── */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    padding-top: 16px;
    border-top: 1px solid #ddd;
    margin-top: 8px;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 14px 32px;
    background: #e74c3c;
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 3px;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-primary:hover:not(:disabled) {
    background: #c0392b;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: transparent;
    color: #888;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 3px;
    text-transform: uppercase;
    text-decoration: none;
    border: 1px solid #ddd;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-secondary:hover {
    color: #1a1a1a;
    border-color: #1a1a1a;
}

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 640px) {
    .editorial-form-section {
        padding: 24px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-primary,
    .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    .field--row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
}
</style>
