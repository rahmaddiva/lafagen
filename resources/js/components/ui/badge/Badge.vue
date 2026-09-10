<script setup>
import { Primitive } from 'radix-vue';
import { cn } from '@/lib/utils';
import { badgeVariants } from '.';

const props = defineProps({
  variant: { type: String, default: 'default' },
  class: { type: null, required: false },
});
</script>

<template>
  <Primitive :class="cn(badgeVariants({ variant }), props.class)">
    <slot />
  </Primitive>
</template>
