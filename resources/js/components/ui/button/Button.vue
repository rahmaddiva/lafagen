<script setup>
import { Primitive } from 'radix-vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '.';

const props = defineProps({
  variant: { type: String, default: 'default' },
  size: { type: String, default: 'default' },
  class: { type: null, required: false },
  asChild: { type: Boolean, default: false },
  as: { type: null, default: 'button' },
});
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :class="cn(buttonVariants({ variant, size }), props.class)"
  >
    <slot />
  </Primitive>
</template>
