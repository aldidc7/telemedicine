<template>
    <div class="loading-container" v-if="isLoading">
        <div class="loader-content">
            <div class="spinner" :class="type"></div>
            <p v-if="message" class="loading-message">{{ message }}</p>
        </div>
        <div v-if="overlay" class="loading-overlay"></div>
    </div>
</template>

<script setup>
defineProps({
    isLoading: {
        type: Boolean,
        required: true,
    },
    message: {
        type: String,
        default: "Loading...",
    },
    type: {
        type: String,
        enum: ["default", "dots", "bars", "pulse"],
        default: "default",
    },
    overlay: {
        type: Boolean,
        default: false,
    },
});
</script>

<style scoped>
.loading-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loader-content {
    position: relative;
    z-index: 10000;
    text-align: center;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

.spinner {
    width: 3rem;
    height: 3rem;
    margin: 0 auto 1rem;
    background: white;
    border-radius: 50%;
}

.spinner.default {
    border: 3px solid #e5e7eb;
    border-top-color: #3b82f6;
    animation: spin 1s linear infinite;
}

.spinner.dots {
    position: relative;
}

.spinner.dots::before,
.spinner.dots::after {
    content: "";
    position: absolute;
    width: 0.75rem;
    height: 0.75rem;
    background: #3b82f6;
    border-radius: 50%;
    animation: pulse 1.5s ease-in-out infinite;
}

.spinner.dots::before {
    left: -1.5rem;
    animation-delay: -0.5s;
}

.spinner.dots::after {
    right: -1.5rem;
}

.spinner.bars {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 2rem;
    gap: 0.375rem;
}

.spinner.bars::before,
.spinner.bars::after {
    content: "";
    display: block;
    width: 0.375rem;
    height: 1.5rem;
    background: #3b82f6;
    border-radius: 0.1875rem;
    animation: scaleUp 1s ease-in-out infinite;
}

.spinner.bars::before {
    animation-delay: -0.2s;
}

.spinner.bars::after {
    animation-delay: 0.2s;
}

.spinner.pulse {
    border: 3px solid #3b82f6;
    animation: pulse 2s ease-in-out infinite;
}

.loading-message {
    color: #374151;
    font-size: 0.875rem;
    margin: 0;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

@keyframes scaleUp {
    0%,
    100% {
        transform: scaleY(1);
    }
    50% {
        transform: scaleY(1.5);
    }
}
</style>
