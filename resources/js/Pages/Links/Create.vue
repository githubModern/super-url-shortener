<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    destination_url: '',
    custom_alias: '',
    campaign_tag: '',
    og_title: '',
    og_description: '',
});

const showAdvanced = ref(false);

const submit = () => {
    form.post(route('links.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Link" />

    <AuthenticatedLayout>
        <template #header>Create Short Link</template>

        <div class="create-wrap">
            <div class="create-card">
                <form @submit.prevent="submit" class="create-form">
                    <!-- Destination URL -->
                    <div class="field">
                        <label class="field__label">Destination URL <span class="field__required">*</span></label>
                        <input
                            v-model="form.destination_url"
                            type="url"
                            placeholder="https://example.com/your-long-url"
                            class="field__input"
                            :class="{ 'field__input--error': form.errors.destination_url }"
                            autofocus
                            required
                        />
                        <span v-if="form.errors.destination_url" class="field__error">{{ form.errors.destination_url }}</span>
                    </div>

                    <!-- Custom Alias -->
                    <div class="field">
                        <label class="field__label">Custom Alias <span class="field__optional">(optional)</span></label>
                        <div class="field__prefix-wrap">
                            <span class="field__prefix">{{ $page.props.ziggy?.url ?? '' }}/</span>
                            <input
                                v-model="form.custom_alias"
                                type="text"
                                placeholder="my-custom-slug"
                                class="field__input field__input--prefixed"
                                :class="{ 'field__input--error': form.errors.custom_alias }"
                                pattern="[a-zA-Z0-9\-_]+"
                                minlength="4"
                                maxlength="20"
                            />
                        </div>
                        <span v-if="form.errors.custom_alias" class="field__error">{{ form.errors.custom_alias }}</span>
                        <span class="field__hint">4–20 characters, letters, numbers, hyphens only</span>
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
                        <Link :href="route('links.index')" class="btn-ghost">Cancel</Link>
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            <span v-if="form.processing">Creating…</span>
                            <span v-else>Create Short Link</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tips Panel -->
            <div class="tips-panel">
                <h3 class="tips-title">Tips</h3>
                <ul class="tips-list">
                    <li>Custom aliases make your links more recognizable and memorable.</li>
                    <li>Campaign tags let you group and filter links in analytics.</li>
                    <li>OG fields control how links appear when shared on social media.</li>
                    <li>Links without expiry stay active indefinitely.</li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.create-wrap {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 24px;
    max-width: 860px;
}

.create-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 28px;
}

.create-form {
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

.field__input--error {
    border-color: #EF4444;
}

.field__textarea {
    resize: vertical;
    min-height: 80px;
}

.field__prefix-wrap {
    display: flex;
    align-items: center;
    background: #0A0A0A;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    overflow: hidden;
    transition: border-color 200ms;
}

.field__prefix-wrap:focus-within {
    border-color: #22D3EE;
}

.field__prefix {
    padding: 10px 12px;
    font-size: 13px;
    color: #52525B;
    white-space: nowrap;
    border-right: 1px solid rgba(255,255,255,0.08);
}

.field__input--prefixed {
    background: transparent;
    border: none;
    border-radius: 0;
    box-shadow: none;
}

.field__input--prefixed:focus {
    border-color: transparent;
    box-shadow: none;
}

.field__error {
    font-size: 12px;
    color: #EF4444;
}

.field__hint {
    font-size: 11px;
    color: #52525B;
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

.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
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

.btn-ghost:hover {
    color: #FAFAFA;
    border-color: rgba(255,255,255,0.2);
}

/* ── Tips Panel ─────────────────────────────────── */
.tips-panel {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 20px;
    height: fit-content;
}

.tips-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #A1A1AA;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 14px;
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-left: 16px;
    margin: 0;
}

.tips-list li {
    font-size: 13px;
    color: #52525B;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .create-wrap {
        grid-template-columns: 1fr;
    }

    .tips-panel { display: none; }
}
</style>
