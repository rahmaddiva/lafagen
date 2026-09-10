<script setup>
import Button from '@/components/ui/button/Button.vue';

defineProps({
    title: { type: String, default: 'Belum ada data' },
    description: { type: String, default: '' },
    actionLabel: { type: String, default: '' },
    actionHref: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed py-16 text-center">
        <p class="text-lg font-semibold">{{ title }}</p>
        <p v-if="description" class="max-w-sm text-sm text-muted-foreground">
            {{ description }}
        </p>
        <Button v-if="actionLabel && actionHref" as-child class="mt-2">
            <a :href="actionHref">{{ actionLabel }}</a>
        </Button>
    </div>
</template>
