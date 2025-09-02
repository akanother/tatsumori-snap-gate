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
            default: 'dark'
        },
    },
    data() {
        return {
            searchQuery: this.modelValue.name || '',
            deliveryDestinations: [],
            filteredDeliveryDestinations: [],
            errorMessage: '',
        };
    },
    methods: {
        async fetchDeliveryDestinations() {
            try {
                const response = await axios.get('/api/Api/get/delivery/info', {
                    withCredentials: true
                });

                if (Array.isArray(response.data.data)) {
                    this.deliveryDestinations = response.data.data.map(destination => ({
                        id: destination.id,
                        name: destination.name
                    }));
                    this.filteredDeliveryDestinations = [...this.deliveryDestinations];
                }
            } catch (error) {
                console.error('得意先データ取得エラー:', error);
            }
        },
        selectDelivery(event) {
            const inputValue = event.target.value.trim();
            const matchedCustomer = this.deliveryDestinations.find(customer =>
                customer.name?.toLowerCase() === inputValue.toLowerCase()
            );

            if (matchedCustomer) {
                this.$emit('update:modelValue', { id: matchedCustomer.id, name: matchedCustomer.name });
            } else {
                this.$emit('update:modelValue', { id: null, name: inputValue });
            }
        },
        handleFocus() {
            if (!Array.isArray(this.deliveryDestinations) || this.deliveryDestinations.length === 0) return;
            this.filteredDeliveryDestinations = [...this.deliveryDestinations];
        },
        handleBlur() {
            this.errorMessage = '';
            const matchedCustomer = this.deliveryDestinations.find(customer =>
                customer.name?.toLowerCase() === this.searchQuery.toLowerCase()
            );

            if (matchedCustomer) {
                this.$emit('update:modelValue', { id: matchedCustomer.id, name: matchedCustomer.name });
            } else {
                this.$emit('update:modelValue', { id: null, name: this.searchQuery });
            }
        },
        clearSelection() {
            this.searchQuery = '';
            this.$emit('update:modelValue', { id: null, name: '' });
            this.errorMessage = '';
        },
    },
    watch: {
        modelValue(newValue) {
            this.searchQuery = newValue?.name || '';
        },
        searchQuery(newQuery) {
            if (!Array.isArray(this.deliveryDestinations)) return;

            if (newQuery.trim() === '') {
                this.filteredDeliveryDestinations = [...this.deliveryDestinations];
            } else {
                this.filteredDeliveryDestinations = this.deliveryDestinations.filter(customer =>
                    customer.name?.toLowerCase().includes(newQuery.toLowerCase())
                );
            }
        },
    },
    mounted() {
        this.fetchDeliveryDestinations();
    },
}
</script>

<template>
    <div>
        <div class="position-relative">
            <label class="text-dark">納入先</label>
        </div>
        <div class="input-group mb-2">
            <input
                type="text"
                class="form-control text-dark"
                :class="{
                    'is-invalid': isInvalid || errorMessage,
                    'is-valid': !isInvalid && modelValue.id
                }"
                v-model="searchQuery"
                @input="searchQuery = $event.target.value"
                @focus="handleFocus"
                @change="selectDelivery"
                @blur="handleBlur"
                placeholder="納入先を検索"
                list="deliveryList"
            />
            <div class="input-group-append">
                <button
                    :class="`btn btn-${theme} px-3 text-white`"
                    @click="clearSelection">
                    <i class="fa fa-close"></i>
                </button>
            </div>
        </div>
        <span v-if="errorMessage" class="input-error-message text-danger" v-text="errorMessage"></span>
        <datalist id="deliveryList">
            <option v-for="delivery in filteredDeliveryDestinations" :key="delivery.id" :value="delivery.name"></option>
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

