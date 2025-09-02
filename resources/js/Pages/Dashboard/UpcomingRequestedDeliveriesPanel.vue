<script>
import BaseKpiCard from './BaseKpiCard.vue';

export default {
    name: 'UpcomingRequestedDeliveriesPanel',
    components: { BaseKpiCard },
    data() {
        return {
            loading: true,
            days: {},          // { 'YYYY-MM-DD': [ {customer_name,...}, ... ] }
            orderedKeys: [],   // 表示順のキー配列
            lastUpdated: null,
            error: null,
            // ★ 拠点フィルタ
            selectedOrganization: '', // ''=全て / '郡山' / '岐阜'
            organizations: ['', '郡山', '岐阜'],
        };
    },
    mounted() { this.fetchData(); },
    computed: {
        totalCount() {
            return Object.values(this.days).reduce((acc, arr) => acc + (arr?.length || 0), 0);
        },
        // 7列グリッドに流し込みやすい形へ（配列）
        dayTiles() {
            return this.orderedKeys.map(k => ({ date: k, items: this.days[k] || [] }));
        },
        orgLabel() {
            return this.selectedOrganization ? `（${this.selectedOrganization}）` : '（全て）';
        }
    },
    methods: {
        async fetchData() {
            this.loading = true; this.error = null;
            try {
                const { data } = await axios.get(
                    route('api.dashboard.upcoming.requested-deliveries'),
                    { params: { organization: this.selectedOrganization || undefined } }
                );
                this.days = data || {};
                this.orderedKeys = Object.keys(this.days).sort();
                this.lastUpdated = new Date();
            } catch (e) {
                console.error(e);
                this.error = '読み込みに失敗しました';
            } finally {
                this.loading = false;
            }
        },
        shortName(name, max = 8) {
            if (!name) return '';
            return name.length <= max ? name : `${name.slice(0, max)}…`;
        },
        toDisp(d) {
            // YYYY-MM-DD -> M/D
            const [y,m,dd] = d.split('-').map(n => parseInt(n,10));
            return `${m}/${dd}`;
        },
        onChangeOrg() {
            if (!this.loading) this.fetchData();
        }
    }
};
</script>

<template>
    <div class="col-sm-6"><!-- 2枠幅。片枠にするなら col-sm-3 -->
        <BaseKpiCard
            :title="`当日以降〜30日 希望納期（受注のみ）${orgLabel}`"
            :loading="loading"
            :lastUpdated="lastUpdated"
            @refresh="fetchData"
        >
            <!-- フィルタ（右寄せ） -->

            <div class="w-100 d-flex justify-content-end mb-2">
                <select
                    class="form-control form-select form-select-sm w-auto"
                    v-model="selectedOrganization"
                    @change="onChangeOrg"
                >
                    <option v-for="o in organizations" :key="o" :value="o">
                        {{ o === '' ? '全て' : o }}
                    </option>
                </select>
            </div>

            <!-- 合計件数 -->
            <div class="fw-bold mb-1">
                <h4 v-if="!loading" class="display-5 text-dark">{{ totalCount }}件</h4>
                <span v-else class="fs-3">...</span>
            </div>

            <!-- セパレーター -->
            <div class="w-100 my-2" style="height:1px;background:rgba(0,0,0,.06)"></div>

            <!-- カレンダーグリッド -->
            <div class="calendar-grid w-100" v-if="!loading">
                <div v-for="tile in dayTiles" :key="tile.date" class="day-tile">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="date-label">{{ toDisp(tile.date) }}</span>
                        <span class="badge badge-light count-badge">{{ tile.items.length }}</span>
                    </div>

                    <!-- 顧客チップ（最大3件、残りは +n） -->
                    <template v-if="tile.items.length">
                        <div class="chips">
                            <span
                                v-for="(it, idx) in tile.items.slice(0,3)"
                                :key="it.order_detail_id"
                                class="chip"
                                :title="it.customer_name + ' / ' + (it.product_name || '')"
                            >
                                {{ shortName(it.customer_name) }}
                            </span>
                            <span
                                v-if="tile.items.length > 3"
                                class="chip more"
                                :title="`${tile.items.length - 3} 件 さらにあります`"
                            >
                                +{{ tile.items.length - 3 }}
                            </span>
                        </div>
                    </template>
                    <div v-else class="text-muted tiny">予定なし</div>
                </div>
            </div>

            <!-- ローディングスケルトン -->
            <div class="calendar-grid w-100" v-else>
                <div v-for="i in 14" :key="i" class="day-tile">
                    <div class="skeleton" style="height:18px;width:60%;margin-bottom:6px;"></div>
                    <div class="skeleton" style="height:20px;width:100%;border-radius:999px;"></div>
                    <div class="skeleton" style="height:20px;width:80%;border-radius:999px;margin-top:6px;"></div>
                </div>
            </div>

            <div v-if="error" class="text-danger small mt-2">{{ error }}</div>
        </BaseKpiCard>
    </div>
</template>

<style scoped>
.calendar-grid{
    display:grid;
    grid-template-columns: repeat(7, 1fr);
    gap:10px;
}
.day-tile{
    border:1px solid #eef1f4;
    border-radius:10px;
    padding:8px;
    min-height:88px;
    background:#fff;
}
.date-label{ font-weight:600; color:#6c757d; }
.count-badge{ font-weight:600; }

/* チップ */
.chips{ display:flex; flex-wrap:wrap; gap:6px; }
.chip{
    display:inline-block;
    background:#f5f7fb;
    border:1px solid #e9edf5;
    color:#d13b8b;
    border-radius:999px;
    padding:2px 8px;
    font-size:12px;
    line-height:1.4;
}
.chip.more{
    background:#fff0f0;
    border-color:#ffd6d6;
    color:#d13b3b;
}

/* 小さめテキスト */
.tiny{ font-size:12px; }

/* スケルトン */
.skeleton{
    background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%);
    background-size:400% 100%;
    animation:shine 1.2s ease infinite;
    border-radius:6px;
}
@keyframes shine{ 0%{background-position:100% 0} 100%{background-position:-100% 0} }
</style>
