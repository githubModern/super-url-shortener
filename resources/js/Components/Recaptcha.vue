<!-- © Atia Hegazy — atiaeno.com -->
<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: String,
    siteKey: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'verified', 'expired', 'error']);
const recaptchaContainer = ref(null);
const widgetId = ref(null);

onMounted(() => {
    // Load reCAPTCHA script if not already loaded
    if (!window.grecaptcha) {
        const script = document.createElement('script');
        script.src = 'https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoad&render=explicit';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);

        window.onRecaptchaLoad = () => {
            renderRecaptcha();
        };
    } else {
        renderRecaptcha();
    }
});

const renderRecaptcha = () => {
    if (window.grecaptcha && recaptchaContainer.value) {
        widgetId.value = window.grecaptcha.render(recaptchaContainer.value, {
            sitekey: props.siteKey,
            callback: onVerify,
            'expired-callback': onExpired,
            'error-callback': onError
        });
    }
};

const onVerify = (token) => {
    emit('update:modelValue', token);
    emit('verified', token);
};

const onExpired = () => {
    emit('update:modelValue', '');
    emit('expired');
};

const onError = () => {
    emit('update:modelValue', '');
    emit('error');
};

const reset = () => {
    if (window.grecaptcha && widgetId.value !== null) {
        window.grecaptcha.reset(widgetId.value);
    }
};

defineExpose({ reset });
</script>

<template>
    <div class="recaptcha-container">
        <div ref="recaptchaContainer" class="g-recaptcha"></div>
        <input
            type="hidden"
            :value="modelValue"
            required
        />
    </div>
</template>

<style scoped>
.recaptcha-container {
    display: flex;
    justify-content: center;
    margin: 1rem 0;
}

.g-recaptcha {
    transform-origin: center;
}

@media (max-width: 480px) {
    .g-recaptcha {
        transform: scale(0.9);
    }
}
</style>
