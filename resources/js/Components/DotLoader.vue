<script>
export default {
    name: 'DotLoader',
    data() {
        return {
            dotCount: 0,
            intervalId: null
        };
    },
    computed: {
        dots() {
            return '.'.repeat(this.dotCount);
        }
    },
    mounted() {
        this.intervalId = setInterval(() => {
            this.dotCount = (this.dotCount + 1) % 4;
        }, 700);
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
    }
}
</script>

<template>
    <span :style="{ display: 'inline-block', minWidth: '2em', textAlign: 'left' }">
        {{ dots }}<span style="opacity:0;">...</span>
    </span>
</template>
