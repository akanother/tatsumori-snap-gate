<template>
  <div v-if="errorMessage" class="input-error-message text-danger">
    {{ errorMessage }}
  </div>
</template>

<script>
export default {
  props: {
    error: {
      type: [String, Array],
      default: ''
    }
  },
  computed: {
    errorMessage() {
      if (Array.isArray(this.error)) {
        return this.error[0] || '';
      }
      return this.error || '';
    }
  }
}
</script>

<style scoped>
.input-error-message {
  width: 100%;
  margin-top: .25rem;
  font-size: 80%;
}
</style>
