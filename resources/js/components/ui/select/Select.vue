<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  class: { type: null, required: false },
});

const model = defineModel({ type: [String, Number, null], required: false });
</script>

<template>
  <select
    v-model="model"
    :class="cn(
      'flex h-9 w-full items-center justify-between rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
      props.class,
    )"
  >
    <slot />
  </select>
</template>
