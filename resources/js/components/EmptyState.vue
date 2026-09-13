<script setup>
import { Inbox } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';

defineProps({
    title: { type: String, default: 'Belum ada data' },
    description: { type: String, default: '' },
    actionLabel: { type: String, default: '' },
    actionHref: { type: String, default: '' },
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed bg-card/50 px-6 py-16 text-center"
    >
        <div
            class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-soft text-primary-strong"
        >
            <slot name="icon">
                <Inbox class="h-6 w-6" aria-hidden="true" />
            </slot>
        </div>
        <p class="text-lg font-bold">{{ title }}</p>
        <p v-if="description" class="max-w-sm text-sm text-muted-foreground">
            {{ description }}
        </p>
        <slot name="action">
            <Button v-if="actionLabel && actionHref" as-child class="mt-1">
                <a :href="actionHref">{{ actionLabel }}</a>
            </Button>
        </slot>
    </div>
</template>
