<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { ref } from 'vue';
import TextInput from './TextInput.vue';

const props = defineProps({
    modelValue: String,
    id: String,
    placeholder: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    autocomplete: {
        type: String,
        default: 'current-password'
    },
    autofocus: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const showPassword = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <div class="password-input-wrap">
        <TextInput
            :id="id"
            :type="showPassword ? 'text' : 'password'"
            class="password-input"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :required="required"
            :autocomplete="autocomplete"
            :autofocus="autofocus"
            :placeholder="placeholder"
        />
        <button
            type="button"
            class="toggle-btn"
            @click="togglePassword"
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
        >
            <!-- Eye icon (showing password) -->
            <svg
                v-if="showPassword"
                class="eye-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                <circle cx="12" cy="12" r="3" />
                <path d="M3 3l18 18" />
            </svg>
            <!-- Eye off icon (hiding password) -->
            <svg
                v-else
                class="eye-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                <circle cx="12" cy="12" r="3" />
            </svg>
        </button>
    </div>
</template>

<style scoped>
.password-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input {
    width: 100%;
    padding-right: 2.75rem;
}

.toggle-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: #999;
    transition: color 200ms ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toggle-btn:hover {
    color: #1a1a1a;
}

.eye-icon {
    width: 18px;
    height: 18px;
}
</style>
