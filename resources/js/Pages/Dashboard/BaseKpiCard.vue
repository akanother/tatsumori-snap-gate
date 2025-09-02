<script>
export default {
    name: 'BaseKpiCard',
    props: {
        title: { type: String, required: true },
        loading: { type: Boolean, default: false },
        lastUpdated: { type: [Date, String, null], default: null }
    },
    emits: ['refresh'],
    computed: {
        timeLabel() {
            if (!this.lastUpdated) return '';
            return this.lastUpdated instanceof Date
                ? this.lastUpdated.toLocaleTimeString()
                : this.lastUpdated;
        }
    },
    methods: {
        refresh() {
            if (!this.loading) this.$emit('refresh');
        }
    }
};
</script>

<template>
    <div class="card widget-flat h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title text-muted fw-normal mb-0">{{ title }}</h6>

            <!-- 右端に配置（更新時刻 + 再読み込み） -->
            <div class="d-flex align-items-center ml-auto" style="gap:12px;">
                <button
                    class="btn btn-sm btn-outline-dark text-nowrap"
                    :disabled="loading"
                    @click="refresh"
                >
                    <span v-if="!loading">再読み込み</span>
                    <span v-else>再読み込み中...</span>
                </button>
                <small v-if="lastUpdated" class="text-muted d-none d-md-inline">
                    更新: {{ timeLabel }}
                </small>
            </div>
        </div>

        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.card.widget-flat .card-header { padding: .6rem .9rem; }

/* 大きな数値用（統一） */
.display-5 { font-size: 2.25rem; font-weight: 700; }

/* スケルトン（必要なカードで利用可） */
.skeleton{
    background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%);
    background-size:400% 100%;
    animation:shine 1.2s ease infinite;
    border-radius:6px;
}
@keyframes shine{ 0%{background-position:100% 0} 100%{background-position:-100% 0} }
</style>
