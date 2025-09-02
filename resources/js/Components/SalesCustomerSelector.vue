<script>
export default {
    props: {
        modelValue: {
            type: Object,
            default: () => ({ id: null, name: '' })
        },
        isInvalid: {
            type: Boolean,
            default: false
        },
        theme: {
            type: String,
            default: 'warning'
        },
    },
    data() {
        return {
            searchQuery: this.modelValue.name || '',
            salesCustomers: [],
            filteredSalesCustomers: [],
            errorMessage: '',  // エラーメッセージを管理
        };
    },
    watch: {
        modelValue(newValue) {
            this.searchQuery = newValue?.name || '';
        },
        searchQuery(newQuery) {
            if (!Array.isArray(this.salesCustomers)) return;

            if (newQuery.trim() === '') {
                this.filteredSalesCustomers = [...this.salesCustomers];
            } else {
                this.filteredSalesCustomers = this.salesCustomers.filter(customer =>
                    customer.name?.toLowerCase().includes(newQuery.toLowerCase())
                );
            }
        }
    },
    methods: {
        /**
         * API から得意先リストを取得
         */
        async fetchSalesCustomers() {
            try {
                const response = await axios.get('/api/Api/get/customer/info', {
                    withCredentials: true
                });

                if (Array.isArray(response.data.data)) {
                    this.salesCustomers = response.data.data.map(customer => ({
                        id: customer.id,
                        name: customer.customer_name
                    }));
                    this.filteredSalesCustomers = [...this.salesCustomers];
                }
            } catch (error) {
                console.error('得意先データ取得エラー:', error);
            }
        },

        /**
         * 🔍 DataList からの選択を処理
         */
        selectCustomerByName() {
            const matchedCustomer = this.salesCustomers.find(customer =>
                customer.name?.toLowerCase() === this.searchQuery.toLowerCase()
            );

            if (matchedCustomer) {
                this.$emit('update:modelValue', { id: matchedCustomer.id, name: matchedCustomer.name });
                this.errorMessage = ''; // ✅ エラーをクリア
            }
        },

        /**
         * **フォーカスアウト時のバリデーション**
         */
        handleBlur() {
            const matchedCustomer = this.salesCustomers.find(customer =>
                customer.name?.toLowerCase() === this.searchQuery.toLowerCase()
            );

            if (matchedCustomer) {
                console.log("✔️ フォーカスアウト時に一致:", matchedCustomer);
                this.$emit('update:modelValue', { id: matchedCustomer.id, name: matchedCustomer.name });
                this.errorMessage = ''; // ✅ エラーをクリア
            } else {
                console.log("✖️ フォーカスアウト時に不一致: リセット");
                this.searchQuery = '';
                this.$emit('update:modelValue', { id: null, name: '' });
                this.errorMessage = 'マスタに存在しない得意先が入力されました';
            }
        },

        /**
         * **クリア処理**
         */
        clearSelection() {
            this.searchQuery = '';
            this.$emit('update:modelValue', { id: null, name: '' });
            this.errorMessage = ''; // ✅ エラーをクリア
        }
    },
    mounted() {
        this.fetchSalesCustomers();
    },
}
</script>

<template>
    <div>
        <label class="text-dark">得意先</label>
        <div class="input-group mb-2">
            <input
                type="text"
                class="form-control text-dark"
                :class="{
                  'is-invalid': isInvalid || errorMessage,
                  'is-valid': !isInvalid && modelValue.id
                }"
                v-model="searchQuery"
                @change="selectCustomerByName"
                @blur="handleBlur"
                placeholder="得意先を検索してください"
                list="salesCustomerList"
            />
            <div class="input-group-append">
                <button
                    :class="`btn btn-${theme} px-3`"
                    style="color:#F8F8F8;" @click="clearSelection"><i class="fa fa-close"></i></button>
            </div>
        </div>
        <span v-if="errorMessage" class="input-error-message text-danger" v-text="errorMessage"></span>
        <datalist id="salesCustomerList">
            <option v-for="customer in filteredSalesCustomers" :key="customer.id" :value="customer.name">
            </option>
        </datalist>
    </div>
</template>

<style scoped>
.input-error-message {
    width: 100%;
    margin-top: .25rem;
    font-size: 80%;
}
</style>
