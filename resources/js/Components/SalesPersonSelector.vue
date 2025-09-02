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
            salesPersons: [],
            filteredSalesPersons: [],
            errorMessage: ''
        };
    },
    watch: {
        modelValue(newValue) {
            this.searchQuery = newValue?.name || '';
        },
        searchQuery(newQuery) {
            if (!Array.isArray(this.salesPersons)) return;

            if (newQuery.trim() === '') {
                this.filteredSalesPersons = [...this.salesPersons];
            } else {
                this.filteredSalesPersons = this.salesPersons.filter(person =>
                    person.name?.toLowerCase().includes(newQuery.toLowerCase())
                );
            }
        }
    },
    methods: {
        /**
         * API から営業担当者リストを取得
         */
        async fetchSalesPersons() {
            try {
                const response = await axios.get('/api/Api/get/user/info', {
                    withCredentials: true
                });

                if (Array.isArray(response.data.data)) {
                    this.salesPersons = response.data.data.map(person => ({
                        id: person.id,
                        name: person.name,
                        department: person.department || '部署不明'
                    }));
                    this.filteredSalesPersons = [...this.salesPersons];
                }
            } catch (error) {
                console.error('営業担当者データ取得エラー:', error);
            }
        },

        /**
         * DataList からの選択処理
         */
        selectPersonByName() {
            const matchedPerson = this.salesPersons.find(person =>
                person.name?.toLowerCase() === this.searchQuery.toLowerCase()
            );

            if (matchedPerson) {
                this.$emit('update:modelValue', { id: matchedPerson.id, name: matchedPerson.name });
                this.errorMessage = ''; // ✅ エラーをクリア
            }
        },

        /**
         * フォーカスアウト時のバリデーション
         */
        handleBlur() {
            const matchedPerson = this.salesPersons.find(person =>
                person.name?.toLowerCase() === this.searchQuery.toLowerCase()
            );

            if (matchedPerson) {
                console.log("✔️ フォーカスアウト時に一致:", matchedPerson);
                this.$emit('update:modelValue', { id: matchedPerson.id, name: matchedPerson.name });
                this.errorMessage = ''; // ✅ エラーをクリア
            } else {
                console.log("✖️ フォーカスアウト時に不一致: リセット");
                this.searchQuery = '';
                this.$emit('update:modelValue', { id: null, name: '' });
                this.errorMessage = 'マスタに存在しない営業担当者が入力されています';
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
        this.fetchSalesPersons();
    },
}
</script>

<template>
    <div>
        <label class="text-dark">営業担当</label>
        <div class="input-group mb-2">
            <input
                type="text"
                class="form-control text-dark"
                :class="{
                    'is-invalid': isInvalid || errorMessage,
                    'is-valid': !isInvalid && modelValue.id
                }"
                v-model="searchQuery"
                @change="selectPersonByName"
                @blur="handleBlur"
                placeholder="営業担当者を検索してください"
                list="salesPersonList"
            />
            <div class="input-group-append">
                <button
                    :class="`btn btn-${theme} px-3`"
                    style="color:#F8F8F8;" @click="clearSelection">
                    <i class="fa fa-close"></i>
                </button>
            </div>
        </div>
        <span v-if="errorMessage" class="input-error-message text-danger">{{ errorMessage }}</span>
        <datalist id="salesPersonList">
            <option v-for="person in filteredSalesPersons" :key="person.id" :value="person.name">
                {{ person.department }}
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
