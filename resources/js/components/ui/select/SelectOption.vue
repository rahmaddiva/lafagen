<script setup>
defineProps({
  value: { type: [String, Number], required: true },
  label: { type: String, required: true },
});
</script>

<template>
  <option :value="value">
    {{ label }}
  </option>
</template>
