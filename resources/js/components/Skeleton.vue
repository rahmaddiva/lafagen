<script setup>
defineProps({
    variant: {
        type: String,
        default: 'text',
        validator: (v) => ['text', 'card', 'chart'].includes(v),
    },
    count: { type: Number, default: 1 },
});
</script>

<template>
    <template v-if="variant === 'card'">
        <div
            v-for="i in count"
            :key="i"
            class="rounded-xl border bg-card p-4 shadow-card"
        >
            <div class="h-8 w-20 animate-pulse rounded-md bg-muted" />
            <div class="mt-2 h-4 w-28 animate-pulse rounded-md bg-muted" />
        </div>
    </template>

    <div
        v-else-if="variant === 'chart'"
        class="h-56 w-full animate-pulse rounded-xl bg-muted"
    />

    <template v-else>
        <div
            v-for="i in count"
            :key="i"
            class="h-4 animate-pulse rounded-md bg-muted"
            :class="count > 1 ? 'mt-2' : ''"
        />
    </template>
</template>
