<script>
import BaseKpiCard from './BaseKpiCard.vue';

export default {
    name: 'LatestUnansweredOrdersPanel',
    components: { BaseKpiCard },
    data() {
        return {
            loading: true,
            backlogTotal: 0,
            newThisMonth: 0,
            newPrevMonth: 0,
            newToday: 0,
            new7days: 0,
            lastUpdated: null,
            error: null,
        };
    },
    mounted() { this.fetchMetrics(); },
    methods: {
        async fetchMetrics() {
            this.loading = true;
            this.error = null;
            try {
                const url = route('dashboard.unanswered.metrics');
                const { data } = await axios.get(url);
                this.backlogTotal = data.backlog_total ?? 0;
                this.newThisMonth = data.new_this_month ?? 0;
                this.newPrevMonth = data.new_prev_month ?? 0;
                this.newToday     = data.new_today ?? 0;
                this.new7days     = data.new_7days ?? 0;
                this.lastUpdated  = new Date();
            } catch (e) {
                console.error('未回答メトリクス取得失敗:', e);
                this.error = '読み込みに失敗しました';
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>

<template>
    <div class="col-sm-3">
        <BaseKpiCard
            title="最新の受注情報（未回答）"
            :loading="loading"
            :lastUpdated="lastUpdated"
            icon="mdi mdi-account-multiple"
            @refresh="fetchMetrics"
        >
            <div class="fw-bold mb-1 bg-dark rounded-lg p-2 ">
                <h4 v-if="!loading" class="text-white">{{ backlogTotal.toLocaleString() }}件</h4>
                <span v-else class="fs-3">...</span>
            </div>

            <div class="w-100 my-2" style="height:1px;background:rgba(0,0,0,.06)"></div>

            <div class="text-muted" style="line-height:1.4">
                今月新規：
                <strong>{{ loading ? '...' : newThisMonth.toLocaleString() }}</strong>
                <div class="small mt-1">
                    今日：{{ loading ? '...' : newToday }}
                    ／ 直近7日：{{ loading ? '...' : new7days }}
                </div>
            </div>
            <p class="mt-1">工場出荷予定・納期が未回答の受注情報を集計しています</p>

            <div v-if="error" class="text-danger small mt-2">{{ error }}</div>
        </BaseKpiCard>
    </div>
</template>
