<!-- resources/js/Pages/Ended.vue -->
<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="text-center p-4" style="max-width: 520px;">
            <div class="mb-3">
                <i class="fas fa-door-closed fa-3x text-muted"></i>
            </div>
            <h1 class="h5 mb-2">セッションを終了しました</h1>
            <p class="text-muted mb-4">{{ message }}</p>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="reload">
                    <i class="fas fa-redo me-1"></i> 再読み込み
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Ended',
    props: {
        message: { type: String, default: '' },
    },
    methods: {
        reload() { window.location.reload(); }
    }
}
</script>
