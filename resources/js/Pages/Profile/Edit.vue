<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    mustVerifyEmail: { type: Boolean },
    status:          { type: String },
    connectedProviders: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// ── Avatar upload (Story 2.6) ─────────────────────────────────────────────
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

// ── OAuth connections (Story 2.8) ─────────────────────────────────────────
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
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- Profile Info -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <!-- Story 2.6: Avatar Upload -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Photo</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Upload a JPG, PNG, or GIF (max 2 MB). Saved at 64, 128, and 256 px.
                            </p>
                        </header>

                        <div class="mt-6 flex items-center gap-6">
                            <!-- Current / preview avatar -->
                            <img
                                :src="avatarPreview ?? user.avatar ?? `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=128&background=0ea5e9&color=fff`"
                                alt="Avatar"
                                class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                            />

                            <div class="flex flex-col gap-3">
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/gif"
                                    class="hidden"
                                    @change="onFileChange"
                                />
                                <button
                                    type="button"
                                    @click="fileInput.click()"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:border-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800"
                                >
                                    Choose Photo
                                </button>

                                <button
                                    v-if="avatarForm.avatar"
                                    type="button"
                                    @click="submitAvatar"
                                    :disabled="avatarForm.processing"
                                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 disabled:opacity-25 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300"
                                >
                                    <span v-if="avatarForm.processing">Saving…</span>
                                    <span v-else>Save Photo</span>
                                </button>
                            </div>
                        </div>

                        <p v-if="avatarForm.errors.avatar" class="mt-2 text-sm text-red-600">{{ avatarForm.errors.avatar }}</p>
                        <p v-if="avatarSuccess" class="mt-2 text-sm text-green-600">Photo updated successfully.</p>
                    </section>
                </div>

                <!-- Story 2.8: Connected Social Accounts -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Connected Accounts</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Manage your social login connections.
                            </p>
                        </header>

                        <ul class="mt-6 divide-y divide-gray-100 dark:divide-gray-700">
                            <li v-for="p in providers" :key="p.key" class="flex items-center justify-between py-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ p.label }}</span>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="text-xs font-semibold"
                                        :class="isConnected(p.key) ? 'text-green-600' : 'text-gray-400'"
                                    >
                                        {{ isConnected(p.key) ? 'Connected' : 'Not connected' }}
                                    </span>
                                    <a
                                        v-if="!isConnected(p.key)"
                                        :href="route('social.redirect', p.key)"
                                        class="text-xs text-indigo-600 hover:underline dark:text-indigo-400"
                                    >Connect</a>
                                    <button
                                        v-else
                                        @click="disconnect(p.key)"
                                        class="text-xs text-red-500 hover:underline"
                                    >Disconnect</button>
                                </div>
                            </li>
                        </ul>
                    </section>
                </div>

                <!-- Update Password -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Delete Account -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <DeleteUserForm class="max-w-xl" />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
