<script>
import { Head } from '@inertiajs/vue3';

export default {
    data() {
        const now = new Date();
        return {
            year: now.getFullYear(),
            month: now.getMonth() + 1,
            orders: [],
            perPage: 2,
            currentPage: 1,
        }
    },
    mounted() {
        this.getOrderMonthlySummary()
    },
    computed: {
        paginatedOrders() {
            const entries = Object.entries(this.orders);
            const start = (this.currentPage - 1) * this.perPage;
            return entries.slice(start, start + this.perPage);
        },
        totalPages() {
            return Math.ceil(Object.keys(this.orders).length / this.perPage);
        },
        // ★ ページ内の件数をカウント
        currentPageRowCount() {
            let count = 0;
            for (const [, products] of this.paginatedOrders) {
                count += Object.keys(products).length;
            }
            return count;
        }
    },
    methods: {
        getOrderMonthlySummary() {
            const url = route('get.order.month.summary');
            axios.get(url)
                .then(response => {
                    this.orders = response.data;
                })
                .catch(error => console.error("データ取得エラー:", error));
        }
    },
    components: {
        Head,
    },
}
</script>

<template>
    <div class="card h-100 d-flex flex-column">
        <div class="card-header">
            <h3 class="card-title">{{ year }}年{{ month }}月の受注状況</h3>
        </div>

        <!-- body部をflexに -->
        <div class="card-body d-flex flex-column">
            <!-- table部分は高さ自動 -->
            <div class="mb-2">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                    <tr>
                        <th>得意先</th>
                        <th>受注製品</th>
                        <th>受注総数量（KG）</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="([customer, products], index) in paginatedOrders" :key="customer + '-group'">
                        <tr v-for="(quantity, productName) in products" :key="customer + '-' + productName">
                            <td>{{ customer }}</td>
                            <td>{{ productName }}</td>
                            <td>{{ quantity }}</td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>

            <!-- アラート部: テーブルの下からフッター直前まで100%広がる -->

            <div v-if="currentPageRowCount < 5" class="alert alert-light text-center flex-grow-1 d-flex align-items-center justify-content-center mb-0">
                このページの件数は {{ currentPageRowCount }} 件です（5件未満）<br>受注登録漏れがないかご確認ください。
            </div>
        </div>

        <!-- footer -->
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
