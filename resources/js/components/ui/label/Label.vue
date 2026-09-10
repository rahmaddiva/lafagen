<script setup>
import { Label } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: { type: null, required: false },
  for: { type: String, required: false },
});
</script>

<template>
  <Label
    :for="props.for"
    :class="cn(
      'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
      props.class,
    )"
  >
    <slot />
  </Label>
</template>
