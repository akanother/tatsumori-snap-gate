<script>
export default {
    name: 'PaidLabel',
    props: {
        value: {
            type: String,
            required: true
        }
    },
    computed: {
        badgeClass() {
            let classes = ['badge', 'p-2'];

            if(this.value === '有償') {
                classes.push('badge-success-inverse');
            } else {
                classes.push('badge-danger-inverse');
            }

            return classes;
        },
        iconClass() {
            return this.value === '有償'
                ? 'fa fa-yen'
                : 'fa fa-gift';
        }
    }
}
</script>

<template>
    <span :class="badgeClass">
        <i :class="iconClass" class="mr-1"></i>{{ value }}
    </span>
</template>
