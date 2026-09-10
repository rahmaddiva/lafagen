<script setup>
const props = defineProps({
    value: { type: Number, required: true },
    label: { type: String, required: true },
});
</script>

<template>
    <div class="rounded-xl border bg-card p-4 shadow-sm">
        <p class="text-3xl font-extrabold">{{ value }}</p>
        <p class="mt-1 text-sm text-muted-foreground">{{ label }}</p>
    </div>
</template>
