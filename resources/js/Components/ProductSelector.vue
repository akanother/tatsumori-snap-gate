<script>
export default {
    name: 'ProductSelector',
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
            default: 'dark'
        },
    },
    data() {
        return {
            searchQuery: this.modelValue.name || '',
            products: [],
            filteredProducts: [],
            errorMessage: ''
        };
    },
    watch: {
        modelValue(newValue) {
            this.searchQuery = newValue?.name || '';
        },
        searchQuery(newQuery) {
            if (!Array.isArray(this.products)) return;

            if (newQuery.trim() === '') {
                this.filteredProducts = [...this.products];
            } else {
                this.filteredProducts = this.products.filter(product =>
                    product.name?.toLowerCase().includes(newQuery.toLowerCase())
                );
            }
        }
    },
    methods: {
        async fetchProducts() {
            try {
                const response = await axios.get('/api/Api/get/product/info', {
                    withCredentials: true
                });

                if (Array.isArray(response.data.data)) {
                    this.products = response.data.data.map(product => ({
                        id: product.id,
                        name: product.product_name
                    }));
                    this.filteredProducts = [...this.products];
                }
            } catch (error) {
                console.error('❌ 製品データ取得エラー:', error);
            }
        },
        selectProductByName() {
            const matchedProduct = this.products.find(product =>
                product.name?.toLowerCase() === this.searchQuery.toLowerCase()
            );

            if (matchedProduct) {
                this.$emit('update:modelValue', { id: matchedProduct.id, name: matchedProduct.name });
                this.errorMessage = '';
            }
        },
        handleBlur() {
            const matchedProduct = this.products.find(product =>
                product.name?.toLowerCase() === this.searchQuery.toLowerCase()
            );

            if (matchedProduct) {
                this.$emit('update:modelValue', { id: matchedProduct.id, name: matchedProduct.name });
                this.errorMessage = '';
            } else {
                this.searchQuery = '';
                this.$emit('update:modelValue', { id: null, name: '' });
                this.errorMessage = 'マスタに存在しない製品が入力されました';
            }
        },
        clearSelection() {
            this.searchQuery = '';
            this.$emit('update:modelValue', { id: null, name: '' });
            this.errorMessage = '';
        }
    },
    mounted() {
        this.fetchProducts();
    },
}
</script>

<template>
    <div>
        <label class="text-dark">製品</label>
        <div class="input-group mb-2">
            <input
                type="text"
                class="form-control text-dark"
                :class="{
                    'is-invalid': isInvalid || errorMessage,
                    'is-valid': !isInvalid && modelValue.id
                }"
                v-model="searchQuery"
                @change="selectProductByName"
                @blur="handleBlur"
                placeholder="製品を検索してください"
                list="productList"
            />
            <div class="input-group-append">
                <button
                    :class="`btn btn-${theme} px-3`"
                    style="color:#F8F8F8;" @click="clearSelection"><i class="fa fa-close"></i></button>
            </div>
        </div>
        <span v-if="errorMessage" class="input-error-message text-danger">{{ errorMessage }}</span>
        <datalist id="productList">
            <option v-for="product in filteredProducts" :key="product.id" :value="product.name"></option>
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
