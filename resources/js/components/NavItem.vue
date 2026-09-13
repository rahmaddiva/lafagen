<script setup>
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

const props = defineProps({
    href: { type: String, required: true },
    label: { type: String, required: true },
    icon: { type: Object, required: true },
    active: { type: Boolean, default: false },
    variant: {
        type: String,
        default: 'sidebar',
        validator: (v) => ['sidebar', 'bottom', 'fab'].includes(v),
    },
});
</script>

<template>
    <!-- Sidebar (desktop) -->
    <Link
        v-if="variant === 'sidebar'"
        :href="href"
        :aria-current="active ? 'page' : undefined"
        :class="
            cn(
                'relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition-colors',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                active
                    ? 'bg-primary-soft text-primary-strong'
                    : 'text-muted-foreground hover:bg-muted hover:text-foreground',
            )
        "
    >
        <span
            v-if="active"
            class="absolute inset-y-1 left-0 w-1 rounded-full bg-primary"
            aria-hidden="true"
        />
        <component :is="icon" class="h-4 w-4 shrink-0" aria-hidden="true" />
        <span class="truncate">{{ label }}</span>
    </Link>

    <!-- Bottom bar (mobile) -->
    <Link
        v-else-if="variant === 'bottom'"
        :href="href"
        :aria-current="active ? 'page' : undefined"
        :class="
            cn(
                'flex min-h-[44px] flex-col items-center justify-center gap-0.5 rounded-lg px-1 py-1.5 text-[10px] font-semibold leading-tight transition-colors',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                active ? 'text-primary' : 'text-muted-foreground',
            )
        "
    >
        <component :is="icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
        <span class="truncate">{{ label }}</span>
    </Link>

    <!-- FAB tengah (mobile) -->
    <Link
        v-else
        :href="href"
        :aria-current="active ? 'page' : undefined"
        class="flex min-h-[44px] flex-col items-center justify-center gap-0.5 px-1 py-1.5 text-[10px] font-semibold leading-tight text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
    >
        <span
            class="flex h-11 w-11 items-center justify-center rounded-full bg-primary shadow-lift transition-all"
            :class="active ? 'bg-primary-strong ring-2 ring-primary ring-offset-2 ring-offset-card' : ''"
        >
            <component :is="icon" class="h-5 w-5" aria-hidden="true" />
        </span>
        <span class="truncate">{{ label }}</span>
    </Link>
</template>
