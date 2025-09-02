<script>
export default {
    name: 'IconCircle',
    props: {
        icon: {
            type: String,
            required: true
        },
        size: {
            type: String,
            default: 'md', // xs, sm, md, lg, xl
        },
        theme: {
            type: String,
            default: 'dark' // danger, info, warning, etc...
        },
    },
    computed: {
        sizeClass() {
            return {
                xs: 'icon-circle-xs',
                sm: 'icon-circle-sm',
                md: 'icon-circle-md',
                lg: 'icon-circle-lg',
                xl: 'icon-circle-xl'
            }[this.size] || 'icon-circle-md';
        },
        themeClass() {
            return `bg-${this.theme}`;
        }
    }
}
</script>

<template>
    <div class="d-flex align-items-baseline">
        <div
            :class="['d-flex', 'align-items-center', 'justify-content-center', 'mr-2', sizeClass, themeClass]"
            style="border-radius:50%;"
        >
            <i :class="icon"></i>
        </div>
        <slot></slot>
    </div>
</template>

<style scoped>
.icon-circle-xs { width: 24px; height: 24px; font-size: .7rem; }
.icon-circle-sm { width: 32px; height: 32px; font-size: 1rem; }
.icon-circle-md { width: 40px; height: 40px; font-size: 1.2rem; }
.icon-circle-lg { width: 56px; height: 56px; font-size: 1.8rem; }
.icon-circle-xl { width: 72px; height: 72px; font-size: 2.5rem; }
</style>
