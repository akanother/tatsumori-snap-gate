<script>
import BaseKpiCard from './BaseKpiCard.vue';

export default {
    name: 'OverdueDeliveryBucketsPanel',
    components: { BaseKpiCard },
    data() {
        return {
            loading: true,
            w1: 0,  // 1〜7日超過
            w2: 0,  // 8〜14日超過
            w4: 0,  // 15〜30日超過
            lastUpdated: null,
            error: null,
        }
    },
    mounted() { this.fetchBuckets(); },
    computed: {
        total() { return this.w1 + this.w2 + this.w4; },
        p1() { return this.total ? Math.round((this.w1 / this.total) * 100) : 0; },
        p2() { return this.total ? Math.round((this.w2 / this.total) * 100) : 0; },
        p4() { return this.total ? Math.round((this.w4 / this.total) * 100) : 0; },
    },
    methods: {
        async fetchBuckets() {
            this.loading = true; this.error = null;
            try {
                const { data } = await axios.get(route('api.dashboard.overdue.buckets'));
                this.w1 = data?.w1 ?? 0;
                this.w2 = data?.w2 ?? 0;
                this.w4 = data?.w4 ?? 0;
                this.lastUpdated = new Date();
            } catch (e) {
                console.error(e);
                this.error = '読み込みに失敗しました';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<template>
    <div class="col-sm-3">
        <BaseKpiCard
            title="納期超過（受注）"
            :loading="loading"
            :lastUpdated="lastUpdated"
            icon="mdi mdi-calendar-alert"
            @refresh="fetchBuckets"
        >
            <!-- 合計 -->
            <div class="fw-bold mb-0 bg-danger rounded-lg p-2">
                <h4 v-if="!loading" class="display-5 text-white">{{ total }}件</h4>
                <span v-else class="fs-3">...</span>
            </div>

            <!-- セパレーター -->
            <div class="w-100 my-2" style="height:1px;background:rgba(0,0,0,.06)"></div>

            <!-- 明細 -->
            <div class="w-100" v-if="!loading">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted"><span class="dot" style="background:#fbc02d"></span>1〜7日</span>
                    <span>
            <span class="badge" style="background:rgba(251,192,45,.15);color:#b28704">{{ p1 }}%</span>
            <strong class="text-danger ml-2">{{ w1 }}</strong>
          </span>
                </div>
                <div class="progress my-1" style="height:5px;"><div class="progress-bar bg-warning" :style="{width: p1 + '%'}"></div></div>

                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="text-muted"><span class="dot" style="background:#ff9f43"></span>8〜14日</span>
                    <span>
            <span class="badge" style="background:rgba(255,159,67,.15);color:#b36a00">{{ p2 }}%</span>
            <strong class="text-danger ml-2">{{ w2 }}</strong>
          </span>
                </div>
                <div class="progress my-1" style="height:5px;"><div class="progress-bar" style="background:#ff9f43" :style="{width: p2 + '%'}"></div></div>

                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="text-muted"><span class="dot" style="background:#ef5350"></span>15〜30日</span>
                    <span>
            <span class="badge" style="background:rgba(239,83,80,.15);color:#b21f2d">{{ p4 }}%</span>
            <strong class="text-danger ml-2">{{ w4 }}</strong>
          </span>
                </div>
                <div class="progress my-1" style="height:5px;"><div class="progress-bar bg-danger" :style="{width: p4 + '%'}"></div></div>
            </div>

            <div v-else class="w-100">
                <div class="skeleton skeleton-line"></div>
                <div class="skeleton skeleton-bar"></div>
                <div class="skeleton skeleton-line"></div>
                <div class="skeleton skeleton-bar"></div>
                <div class="skeleton skeleton-line"></div>
                <div class="skeleton skeleton-bar"></div>
            </div>

            <div v-if="error" class="text-danger small mt-1">{{ error }}</div>
        </BaseKpiCard>
    </div>
</template>

<style scoped>
.dot{ width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:6px; }
.skeleton{ background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%); background-size:400% 100%;
    animation:shine 1.2s ease infinite; border-radius:6px; }
.skeleton-line{ height:16px; width:85%; }
.skeleton-bar{ height:5px; width:100%; border-radius:20px; }
@keyframes shine{ 0%{background-position:100% 0} 100%{background-position:-100% 0} }
</style>
