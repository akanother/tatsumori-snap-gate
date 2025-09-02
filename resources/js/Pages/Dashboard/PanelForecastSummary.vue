<script>
import { Head } from '@inertiajs/vue3';

export default {
    data() {
        const now = new Date();
        return {
            year: now.getFullYear(),
            month: now.getMonth() + 1,
            orders: {},   // { 'YYYY-MM': { 顧客名: { 製品名: '数量(kg文字列)' } } }
            perPage: 2,
            currentPage: 1,
        };
    },
    mounted() {
        this.getOrderMonthlySummary();
    },
    computed: {
        paginatedOrders() {
            const entries = Object.entries(this.orders); // [['2025-08', { KCC: { LER: '300.00 kg' } }], ...]
            const start = (this.currentPage - 1) * this.perPage;
            return entries.slice(start, start + this.perPage);
        },
        totalPages() {
            const len = Object.keys(this.orders).length || 1;
            return Math.ceil(len / this.perPage);
        },
        // 現在ページのテーブル行数（= 顧客×製品の総数）
        currentPageRowCount() {
            let count = 0;
            for (const [, customers] of this.paginatedOrders) {
                for (const products of Object.values(customers)) {
                    count += Object.keys(products || {}).length;
                }
            }
            return count;
        }
    },
    methods: {
        getOrderMonthlySummary() {
            const url = route('get.forecast.month.summary');
            axios.get(url)
                .then(response => {
                    this.orders = response.data || {};
                })
                .catch(error => console.error("データ取得エラー:", error));
        }
    },
    components: { Head },
};
</script>

<template>
    <div class="card h-100 d-flex flex-column">
        <div class="card-header">
            <h3 class="card-title">{{ year }}年{{ month }}月の内示状況（四半期）</h3>
        </div>

        <!-- 本文：flex縦積み。テーブルの下にアラートを敷き詰める -->
        <div class="card-body d-flex flex-column">
            <!-- テーブル部分（高さは内容に応じて） -->
            <div class="mb-2">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                    <tr>
                        <th>予定年月</th>
                        <th>得意先</th>
                        <th>製品</th>
                        <th>数量</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="([yyyymm, customers], idx) in paginatedOrders" :key="yyyymm + '-' + idx">
                        <template v-for="(products, customer) in customers" :key="yyyymm + '-' + customer">
                            <tr v-for="(quantity, productName) in products" :key="yyyymm + '-' + customer + '-' + productName">
                                <td>{{ yyyymm }}</td>
                                <td>{{ customer }}</td>
                                <td>{{ productName }}</td>
                                <td>{{ quantity }}</td>
                            </tr>
                        </template>
                    </template>
                    </tbody>
                </table>
            </div>

            <!-- アラート：テーブル直下〜フッター直前まで100%に拡張 -->
            <div
                v-if="currentPageRowCount < 5"
                class="alert alert-light mb-0 flex-grow-1 d-flex align-items-center justify-content-center text-center"
            >
                このページの件数は {{ currentPageRowCount }} 件です（5件未満）<br>受注登録漏れや入力状況をご確認ください。
            </div>
        </div>

        <!-- フッター（そのまま） -->
        <div class="card-footer clearfix">
            <ul class="pagination pagination-sm m-0 float-right">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link" @click="currentPage--" :disabled="currentPage === 1">«</button>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">{{ currentPage }} / {{ totalPages }}</span>
                </li>
                <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
                    <button class="page-link" @click="currentPage++" :disabled="currentPage >= totalPages">»</button>
                </li>
            </ul>
        </div>
    </div>
</template>
