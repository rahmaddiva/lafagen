<script setup>
import { computed } from 'vue';
import { TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    value: { type: Number, required: true },
    label: { type: String, required: true },
    icon: { type: Object, default: null },
    tone: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'success', 'warning', 'info'].includes(v),
    },
    /** { current, previous } — dua-duanya angka; badge delta tak dirender jika null. */
    delta: { type: Object, default: null },
    hint: { type: String, default: '' },
});

const toneChip = {
    primary: 'bg-primary-soft text-primary-strong',
    success: 'bg-success-soft text-success-strong',
    warning: 'bg-warning-soft text-warning-strong',
    info: 'bg-info-soft text-info-strong',
};

/**
 * Delta dinyatakan hati-hati: pembagian dengan 0 tidak pernah terjadi, dan
 * "naik dari 0" bukan persentase melainkan kondisi baru.
 */
const trend = computed(() => {
    if (!props.delta) return null;
    const cur = Number(props.delta.current) || 0;
    const prev = Number(props.delta.previous) || 0;

    if (prev === 0 && cur === 0) return { dir: 'flat', text: 'tetap', sr: 'tidak berubah' };
    if (prev === 0) return { dir: 'up', text: 'baru', sr: `naik dari 0 menjadi ${cur}` };

    const pct = Math.round(((cur - prev) / prev) * 100);
    if (pct === 0) return { dir: 'flat', text: '±0%', sr: 'tidak berubah' };
    return {
        dir: pct > 0 ? 'up' : 'down',
        text: `${pct > 0 ? '+' : '−'}${Math.abs(pct)}%`,
        sr: pct > 0 ? `naik ${Math.abs(pct)} persen` : `turun ${Math.abs(pct)} persen`,
    };
});

const TrendIcon = computed(() =>
    trend.value?.dir === 'up' ? TrendingUp : trend.value?.dir === 'down' ? TrendingDown : Minus,
);

const trendClass = computed(() => {
    if (!trend.value) return '';
    return {
        up: 'bg-success-soft text-success-strong',
        down: 'bg-destructive/10 text-destructive',
        flat: 'bg-muted text-muted-foreground',
    }[trend.value.dir];
});
</script>

<template>
    <div class="rounded-xl border bg-card p-4 shadow-card">
        <div class="flex items-start justify-between gap-3">
            <p class="text-sm font-medium leading-tight text-muted-foreground">{{ label }}</p>
            <span
                v-if="icon"
                :class="cn('flex h-9 w-9 shrink-0 items-center justify-center rounded-lg', toneChip[tone])"
                aria-hidden="true"
            >
                <component :is="icon" class="h-[18px] w-[18px]" />
            </span>
        </div>

        <p class="mt-2 text-3xl font-extrabold tabular-nums tracking-tight text-foreground">
            {{ value.toLocaleString('id-ID') }}
        </p>

        <div class="mt-1.5 flex min-h-[22px] items-center gap-2">
            <span
                v-if="trend"
                :class="cn('inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold', trendClass)"
            >
                <component :is="TrendIcon" class="h-3.5 w-3.5" aria-hidden="true" />
                {{ trend.text }}
                <span class="sr-only">{{ trend.sr }}</span>
            </span>
            <span v-if="hint" class="truncate text-xs text-muted-foreground">{{ hint }}</span>
        </div>
    </div>
</template>
