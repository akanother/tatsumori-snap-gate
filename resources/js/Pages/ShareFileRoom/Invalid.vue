<template>
    <div class="container py-5 text-center">
        <h1 class="text-danger">
            <i class="fas fa-exclamation-triangle"></i> 無効なURL
        </h1>
        <p class="mt-3">{{ message }}</p>
    </div>
</template>

<script setup>
const props = defineProps({
    message: {
        type: String,
        default: 'この共有リンクは無効、または期限切れです。'
    }
})
</script>
