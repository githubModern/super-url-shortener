<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    link: { type: Object, required: true },
});

const form = useForm({
    destination_url: props.link.destination_url ?? '',
    campaign_tag: props.link.campaign_tag ?? '',
    og_title: props.link.og_title ?? '',
    og_description: props.link.og_description ?? '',
    is_active: props.link.is_active ?? true,
});

const showAdvanced = ref(!!(props.link.og_title || props.link.og_description));

const submit = () => {
    form.patch(route('links.update', props.link.id));
};
</script>

<template>
    <Head :title="`Edit /${link.short_code}`" />

    <AuthenticatedLayout>
        <template #header>Edit Link</template>

        <div class="edit-wrap">
            <div class="edit-card">
                <!-- Short code badge -->
                <div class="short-badge">
                    <span class="short-badge__label">Short URL</span>
                    <a :href="`/${link.short_code}`" target="_blank" class="short-badge__code">
                        /{{ link.short_code }}
                    </a>
                    <span v-if="link.custom_alias" class="short-badge__alias">(custom alias)</span>
                </div>

                <form @submit.prevent="submit" class="edit-form">
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
                        <label class="field__label">Link Status</label>
                        <label class="toggle">
                            <input type="checkbox" v-model="form.is_active" class="toggle__input" />
                            <span class="toggle__track">
                                <span class="toggle__thumb" />
                            </span>
                            <span class="toggle__label">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>

                    <!-- Advanced OG toggle -->
                    <button type="button" class="toggle-advanced" @click="showAdvanced = !showAdvanced">
                        <span>{{ showAdvanced ? '▲' : '▼' }} Advanced (OG / Social Preview)</span>
                    </button>

                    <div v-if="showAdvanced" class="advanced-fields">
                        <div class="field">
                            <label class="field__label">OG Title</label>
                            <input v-model="form.og_title" type="text" placeholder="Override link preview title" class="field__input" maxlength="255" />
                        </div>
                        <div class="field">
                            <label class="field__label">OG Description</label>
                            <textarea v-model="form.og_description" placeholder="Override link preview description" class="field__input field__textarea" rows="3" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <Link :href="route('links.show', link.id)" class="btn-ghost">Cancel</Link>
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
.edit-wrap {
    max-width: 580px;
}

.edit-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 28px;
}

.short-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: rgba(34,211,238,0.05);
    border: 1px solid rgba(34,211,238,0.15);
    border-radius: 8px;
    margin-bottom: 24px;
}

.short-badge__label {
    font-size: 11px;
    font-weight: 600;
    color: #52525B;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.short-badge__code {
    font-family: 'Space Grotesk', monospace;
    font-size: 15px;
    font-weight: 700;
    color: #22D3EE;
    text-decoration: none;
}

.short-badge__alias {
    font-size: 11px;
    color: #52525B;
}

.edit-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ── Fields ─────────────────────────────────────── */
.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field--row {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
}

.field__label {
    font-size: 13px;
    font-weight: 500;
    color: #A1A1AA;
}

.field__required { color: #EF4444; margin-left: 2px; }
.field__optional { color: #52525B; font-weight: 400; }

.field__input {
    background: #0A0A0A;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    color: #FAFAFA;
    outline: none;
    transition: border-color 200ms;
    font-family: inherit;
    width: 100%;
}

.field__input:focus {
    border-color: #22D3EE;
    box-shadow: 0 0 0 3px rgba(34,211,238,0.08);
}

.field__input--error { border-color: #EF4444; }
.field__textarea { resize: vertical; min-height: 80px; }
.field__error { font-size: 12px; color: #EF4444; }

/* ── Toggle switch ───────────────────────────────── */
.toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.toggle__input {
    display: none;
}

.toggle__track {
    width: 40px;
    height: 22px;
    background: rgba(255,255,255,0.1);
    border-radius: 11px;
    display: flex;
    align-items: center;
    padding: 2px;
    transition: background 200ms;
    position: relative;
}

.toggle__input:checked + .toggle__track {
    background: #22D3EE;
}

.toggle__thumb {
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform 200ms;
}

.toggle__input:checked + .toggle__track .toggle__thumb {
    transform: translateX(18px);
}

.toggle__label {
    font-size: 13px;
    color: #A1A1AA;
}

/* ── Advanced toggle ─────────────────────────────── */
.toggle-advanced {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 12px;
    color: #71717A;
    text-align: left;
    padding: 0;
    transition: color 200ms;
}

.toggle-advanced:hover { color: #A1A1AA; }

.advanced-fields {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 20px;
    background: rgba(255,255,255,0.02);
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.05);
}

/* ── Actions ─────────────────────────────────────── */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 8px;
    border-top: 1px solid rgba(255,255,255,0.05);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
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

.btn-ghost {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background: transparent;
    color: #71717A;
    font-size: 14px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.08);
    text-decoration: none;
    transition: all 200ms;
}

.btn-ghost:hover { color: #FAFAFA; border-color: rgba(255,255,255,0.2); }
</style>
