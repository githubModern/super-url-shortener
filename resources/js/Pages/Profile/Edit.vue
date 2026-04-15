<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    mustVerifyEmail: { type: Boolean },
    status:          { type: String },
    connectedProviders: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// ── Profile Information Form ───────────────────────────────────────────────
const profileForm = useForm({
    name: user.value.name,
    email: user.value.email,
});

const submitProfile = () => {
    profileForm.patch(route('profile.update'));
};

// ── Password Form ──────────────────────────────────────────────────────────
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

// ── Avatar upload ───────────────────────────────────────────────────────────
const avatarForm    = useForm({ avatar: null });
const avatarPreview = ref(null);
const fileInput     = ref(null);
const avatarSuccess = computed(() => props.status === 'avatar-updated');

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarForm.avatar = file;
    const reader = new FileReader();
    reader.onload = (ev) => { avatarPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const submitAvatar = () => {
    avatarForm.post(route('profile.avatar'), {
        forceFormData: true,
        onSuccess: () => { avatarPreview.value = null; },
    });
};

// ── Delete Account ───────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deletePassword = ref('');
const deleteForm = useForm({ password: '' });

const confirmDelete = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => { showDeleteModal.value = false; deleteForm.reset(); },
        onError: () => { deleteForm.reset('password'); },
    });
};

// ── OAuth connections ───────────────────────────────────────────────────────
const providers = [
    { key: 'google',   label: 'Google' },
    { key: 'github',   label: 'GitHub' },
    { key: 'facebook', label: 'Facebook' },
];

const isConnected = (provider) => props.connectedProviders.includes(provider);

const disconnect = (provider) => {
    if (!confirm(`Disconnect ${provider}?`)) return;
    router.delete(route('social.disconnect', provider));
};
</script>

<template>
    <Head title="Profile — Settings" />

    <div class="editorial">
        <!-- Fixed masthead -->
        <header class="masthead">
            <div class="masthead-logo">ShortLink</div>
            <nav class="masthead-nav">
                <a :href="route('dashboard')">Dashboard</a>
                <a :href="route('profile.edit')" class="active">Profile</a>
            </nav>
        </header>

        <!-- Hero -->
        <section class="hero-split">
            <div class="hero-left">
                <div class="issue-label">User Settings</div>
                <h1>Profile<span>Management</span></h1>
                <p class="deck">Configure your account preferences, update personal information, and manage security settings with editorial precision.</p>
            </div>

            <div class="hero-right">
                <div class="blueprint">
                    <h3>Account Overview</h3>
                    <div class="spec-list">
                        <div class="spec-item">
                            <span class="spec-num">01</span>
                            <span class="spec-label">Member Since</span>
                            <span class="spec-val">{{ new Date(user.created_at).getFullYear() }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-num">02</span>
                            <span class="spec-label">Account Status</span>
                            <span class="spec-val">Active</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-num">03</span>
                            <span class="spec-label">Email</span>
                            <span class="spec-val">{{ user.email_verified_at ? 'Verified' : 'Pending' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profile Information -->
        <section class="form-section">
            <div class="form-header">
                <span class="roman-num">I.</span>
                <h3>Profile Information</h3>
                <p>Update your account's profile information and email address.</p>
            </div>

            <form @submit.prevent="submitProfile" class="editorial-form">
                <div class="form-row">
                    <div class="form-field">
                        <label>Name</label>
                        <input
                            v-model="profileForm.name"
                            type="text"
                            required
                            autocomplete="name"
                            class="editorial-input"
                            placeholder="Your name"
                        />
                        <span v-if="profileForm.errors.name" class="form-error">{{ profileForm.errors.name }}</span>
                    </div>

                    <div class="form-field">
                        <label>Email Address</label>
                        <input
                            v-model="profileForm.email"
                            type="email"
                            required
                            autocomplete="username"
                            class="editorial-input"
                            placeholder="your@email.com"
                        />
                        <span v-if="profileForm.errors.email" class="form-error">{{ profileForm.errors.email }}</span>
                    </div>
                </div>

                <div v-if="mustVerifyEmail && user.email_verified_at === null" class="verification-notice">
                    <p>Your email address is unverified.</p>
                    <a :href="route('verification.send')" method="post" as="button">Click here to re-send the verification email.</a>
                    <span v-if="status === 'verification-link-sent'" class="success-msg">A new verification link has been sent.</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" :disabled="profileForm.processing">
                        {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <span v-if="profileForm.recentlySuccessful" class="success-indicator">Saved successfully.</span>
                </div>
            </form>
        </section>

        <!-- Profile Photo -->
        <section class="form-section photo-section">
            <div class="form-header">
                <span class="roman-num">II.</span>
                <h3>Profile Photo</h3>
                <p>Upload a JPG, PNG, or GIF (max 2 MB). Saved at 64, 128, and 256 px.</p>
            </div>

            <div class="photo-upload">
                <img
                    :src="avatarPreview ?? user.avatar ?? `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=128&background=1a1a1a&color=d4af37`"
                    alt="Avatar"
                    class="avatar-preview"
                />

                <div class="photo-actions">
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/gif"
                        class="hidden"
                        @change="onFileChange"
                    />
                    <button type="button" @click="fileInput.click()" class="btn-secondary">
                        Choose Photo
                    </button>
                    <button
                        v-if="avatarForm.avatar"
                        type="button"
                        @click="submitAvatar"
                        :disabled="avatarForm.processing"
                        class="btn-primary"
                    >
                        <span v-if="avatarForm.processing">Saving…</span>
                        <span v-else>Save Photo</span>
                    </button>
                </div>
            </div>

            <p v-if="avatarForm.errors.avatar" class="form-error">{{ avatarForm.errors.avatar }}</p>
            <p v-if="avatarSuccess" class="success-msg">Photo updated successfully.</p>
        </section>

        <!-- Connected Accounts -->
        <section class="form-section">
            <div class="form-header">
                <span class="roman-num">III.</span>
                <h3>Connected Accounts</h3>
                <p>Manage your social login connections.</p>
            </div>

            <div class="providers-list">
                <div v-for="p in providers" :key="p.key" class="provider-item">
                    <span class="provider-name">{{ p.label }}</span>
                    <div class="provider-status">
                        <span :class="['status-badge', isConnected(p.key) ? 'connected' : 'disconnected']">
                            {{ isConnected(p.key) ? 'Connected' : 'Not connected' }}
                        </span>
                        <a v-if="!isConnected(p.key)" :href="route('social.redirect', p.key)" class="connect-link">Connect</a>
                        <button v-else @click="disconnect(p.key)" class="disconnect-btn">Disconnect</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Update Password -->
        <section class="form-section">
            <div class="form-header">
                <span class="roman-num">IV.</span>
                <h3>Update Password</h3>
                <p>Ensure your account is using a long, random password to stay secure.</p>
            </div>

            <form @submit.prevent="updatePassword" class="editorial-form">
                <div class="form-row">
                    <div class="form-field">
                        <label>Current Password</label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            class="editorial-input"
                            placeholder="••••••••"
                        />
                        <span v-if="passwordForm.errors.current_password" class="form-error">{{ passwordForm.errors.current_password }}</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label>New Password</label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            class="editorial-input"
                            placeholder="••••••••"
                        />
                        <span v-if="passwordForm.errors.password" class="form-error">{{ passwordForm.errors.password }}</span>
                    </div>

                    <div class="form-field">
                        <label>Confirm Password</label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="editorial-input"
                            placeholder="••••••••"
                        />
                        <span v-if="passwordForm.errors.password_confirmation" class="form-error">{{ passwordForm.errors.password_confirmation }}</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" :disabled="passwordForm.processing">
                        {{ passwordForm.processing ? 'Saving...' : 'Update Password' }}
                    </button>
                    <span v-if="passwordForm.recentlySuccessful" class="success-indicator">Password updated.</span>
                </div>
            </form>
        </section>

        <!-- Delete Account -->
        <section class="form-section danger-section">
            <div class="form-header">
                <span class="roman-num">V.</span>
                <h3>Delete Account</h3>
                <p>Once your account is deleted, all of its resources and data will be permanently deleted.</p>
            </div>

            <button @click="showDeleteModal = true" class="btn-danger">Delete Account</button>
        </section>

        <!-- Editorial Footer -->
        <footer class="editorial-footer">
            <p>© MMXXV ShortLink — User Settings</p>
            <a :href="route('dashboard')" class="footer-link">Return to Dashboard</a>
        </footer>

        <!-- Delete Account Modal -->
        <div v-if="showDeleteModal" class="modal-overlay">
            <div class="modal-panel">
                <h3 class="modal-title">Delete Account</h3>
                <p class="modal-desc">Are you sure you want to delete your account? This action cannot be undone. Please enter your password to confirm.</p>

                <div class="modal-field">
                    <label>Password</label>
                    <input
                        v-model="deleteForm.password"
                        type="password"
                        class="editorial-input"
                        placeholder="Enter your password"
                        @keyup.enter="confirmDelete"
                    />
                    <span v-if="deleteForm.errors.password" class="form-error">{{ deleteForm.errors.password }}</span>
                </div>

                <div class="modal-actions">
                    <button @click="showDeleteModal = false; deleteForm.reset();" class="btn-ghost">Cancel</button>
                    <button @click="confirmDelete" :disabled="deleteForm.processing" class="btn-danger">
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete Account' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── DECONSTRUCTIVIST EDITORIAL ───────────────────────────── */

@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Oswald:wght@500;700&display=swap');

* {
    box-sizing: border-box;
}

.editorial {
    font-family: 'Crimson Pro', serif;
    background: #fafafa;
    color: #1a1a1a;
    min-height: 100vh;
}

/* ── Masthead Navigation ───────────────────────────────────── */
.masthead {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    padding: 20px 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    mix-blend-mode: difference;
    z-index: 100;
    color: #fff;
}

.masthead-logo {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 8px;
    text-transform: uppercase;
}

.masthead-nav {
    display: flex;
    gap: 40px;
}

.masthead-nav a {
    color: inherit;
    text-decoration: none;
    font-size: 12px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: 'Oswald', sans-serif;
    position: relative;
}

.masthead-nav a::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 1px;
    background: currentColor;
    transition: width 0.3s;
}

.masthead-nav a:hover::after,
.masthead-nav a.active::after {
    width: 100%;
}

/* ── Split Hero Layout ────────────────────────────────────── */
.hero-split {
    min-height: 70vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    position: relative;
}

.hero-left {
    padding: 200px 80px 80px;
    position: relative;
}

.issue-label {
    position: absolute;
    top: 120px;
    left: 80px;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #999;
    transform: rotate(-90deg);
    transform-origin: left top;
}

h1 {
    font-family: 'Oswald', sans-serif;
    font-size: 100px;
    font-weight: 700;
    line-height: 0.9;
    letter-spacing: -4px;
    margin-bottom: 30px;
}

h1 span {
    display: block;
    font-family: 'Crimson Pro', serif;
    font-size: 48px;
    font-weight: 400;
    font-style: italic;
    letter-spacing: 0;
    margin-top: 16px;
    color: #e74c3c;
}

.deck {
    font-size: 20px;
    line-height: 1.5;
    max-width: 400px;
    margin-bottom: 40px;
    color: #666;
}

/* ── Right Panel / Blueprint ───────────────────────────────── */
.hero-right {
    background: #1a1a1a;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 80px;
}

.hero-right::before {
    content: '';
    position: absolute;
    top: 60px;
    right: 60px;
    bottom: 60px;
    left: 60px;
    border: 1px solid #333;
}

.blueprint {
    width: 100%;
    max-width: 400px;
    position: relative;
    z-index: 1;
    color: #888;
}

.blueprint h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 20px;
}

/* ── Spec List (Numbered Rows with Gold Values) ───────────── */
.spec-list {
    border-top: 1px solid #333;
    padding-top: 20px;
}

.spec-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 12px 0;
    border-bottom: 1px solid #222;
}

.spec-num {
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    color: #555;
    width: 30px;
}

.spec-label {
    flex: 1;
    font-size: 14px;
    color: #888;
}

.spec-val {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    color: #d4af37;
    letter-spacing: 2px;
}

/* ── Form Sections ────────────────────────────────────────── */
.form-section {
    background: #fafafa;
    padding: 80px 60px;
    border-top: 1px solid #eee;
}

.form-section:nth-child(odd) {
    background: #f0f0f0;
}

.form-header {
    max-width: 800px;
    margin: 0 auto 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.form-header h3 {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0 0 12px;
    display: inline-block;
}

.roman-num {
    font-size: 12px;
    font-weight: 700;
    color: #e74c3c;
    letter-spacing: 1px;
    margin-right: 12px;
}

.form-header p {
    font-size: 16px;
    color: #666;
    margin: 0;
}

/* ── Editorial Flat Form Style ─────────────────────────────── */
.editorial-form {
    max-width: 800px;
    margin: 0 auto;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 24px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-field label {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #666;
}

.editorial-input {
    padding: 16px 20px;
    border: 1px solid #ddd;
    background: #fff;
    font-family: 'Crimson Pro', serif;
    font-size: 18px;
    color: #1a1a1a;
    outline: none;
    transition: all 0.2s;
}

.editorial-input:focus {
    border-color: #1a1a1a;
}

.editorial-input::placeholder {
    color: #bbb;
    font-style: italic;
}

.form-error {
    font-size: 14px;
    color: #e74c3c;
    font-style: italic;
}

/* ── Photo Section ──────────────────────────────────────────── */
.photo-upload {
    max-width: 800px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 30px;
}

.avatar-preview {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border: 2px solid #1a1a1a;
}

.photo-actions {
    display: flex;
    gap: 16px;
}

.hidden {
    display: none;
}

/* ── Buttons ────────────────────────────────────────────────── */
.form-actions {
    max-width: 800px;
    margin: 30px auto 0;
    display: flex;
    align-items: center;
    gap: 20px;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: #e74c3c;
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
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
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: transparent;
    color: #1a1a1a;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    border: 1px solid #1a1a1a;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #1a1a1a;
    color: #fff;
}

.btn-danger {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: transparent;
    color: #e74c3c;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    border: 1px solid #e74c3c;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-danger:hover:not(:disabled) {
    background: #e74c3c;
    color: #fff;
}

.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-ghost {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    background: transparent;
    color: #666;
    font-family: 'Oswald', sans-serif;
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    border: 1px solid #ddd;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-ghost:hover {
    border-color: #1a1a1a;
    color: #1a1a1a;
}

.success-indicator {
    font-size: 14px;
    color: #27ae60;
    font-style: italic;
}

.success-msg {
    font-size: 14px;
    color: #27ae60;
    margin-top: 12px;
}

/* ── Verification Notice ──────────────────────────────────── */
.verification-notice {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: rgba(231, 76, 60, 0.05);
    border-left: 3px solid #e74c3c;
}

.verification-notice p {
    margin: 0 0 8px;
    font-size: 16px;
}

.verification-notice a {
    color: #e74c3c;
    text-decoration: none;
    font-weight: 600;
}

.verification-notice a:hover {
    text-decoration: underline;
}

/* ── Providers List ────────────────────────────────────────── */
.providers-list {
    max-width: 800px;
    margin: 0 auto;
}

.provider-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 0;
    border-bottom: 1px solid #ddd;
}

.provider-name {
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.provider-status {
    display: flex;
    align-items: center;
    gap: 20px;
}

.status-badge {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 4px 12px;
    border: 1px solid;
}

.status-badge.connected {
    color: #27ae60;
    border-color: #27ae60;
}

.status-badge.disconnected {
    color: #999;
    border-color: #ddd;
}

.connect-link {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #1a1a1a;
    text-decoration: none;
    padding: 8px 16px;
    border: 1px solid #1a1a1a;
    transition: all 0.2s;
}

.connect-link:hover {
    background: #1a1a1a;
    color: #fff;
}

.disconnect-btn {
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #e74c3c;
    background: transparent;
    border: 1px solid #e74c3c;
    padding: 8px 16px;
    cursor: pointer;
    transition: all 0.2s;
}

.disconnect-btn:hover {
    background: #e74c3c;
    color: #fff;
}

/* ── Danger Section ─────────────────────────────────────────── */
.danger-section {
    background: #fff;
    border-top: 3px solid #e74c3c;
}

/* ── Editorial Footer ──────────────────────────────────────── */
.editorial-footer {
    background: #1a1a1a;
    color: #666;
    padding: 60px;
    text-align: center;
    font-size: 14px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-family: 'Oswald', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 40px;
    flex-wrap: wrap;
}

.footer-link {
    color: #888;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-link:hover {
    color: #d4af37;
}

/* ── Modal Styles ──────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 200;
    padding: 20px;
}

.modal-panel {
    background: #fafafa;
    max-width: 480px;
    width: 100%;
    padding: 40px;
    position: relative;
}

.modal-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: #e74c3c;
}

.modal-title {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 0 0 24px;
}

.modal-desc {
    font-size: 16px;
    line-height: 1.6;
    color: #666;
    margin-bottom: 24px;
}

.modal-field {
    margin-bottom: 24px;
}

.modal-field label {
    display: block;
    font-family: 'Oswald', sans-serif;
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #666;
    margin-bottom: 8px;
}

.modal-actions {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 968px) {
    .masthead {
        padding: 20px 30px;
    }

    .hero-split {
        grid-template-columns: 1fr;
    }

    .hero-left {
        padding: 140px 30px 60px;
    }

    .issue-label {
        top: 100px;
        left: 30px;
    }

    h1 {
        font-size: 60px;
    }

    h1 span {
        font-size: 36px;
    }

    .hero-right {
        padding: 60px 30px;
    }

    .form-section {
        padding: 60px 30px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .photo-upload {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 48px;
    }

    .masthead-nav {
        gap: 20px;
    }

    .form-actions {
        flex-direction: column;
        align-items: flex-start;
    }

    .provider-item {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }

    .modal-actions {
        flex-direction: column;
    }
}
</style>
