<script setup>
import { Separator } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: { type: null, required: false },
  orientation: { type: String, default: 'horizontal' },
  decorative: { type: Boolean, default: true },
});
</script>

<template>
  <Separator
    :orientation="orientation"
    :decorative="decorative"
    :class="cn('shrink-0 bg-border', orientation === 'horizontal' ? 'h-[1px] w-full' : 'w-[1px] self-stretch', props.class)"
  />
</template>
