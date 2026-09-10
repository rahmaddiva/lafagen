<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Array, default: () => [] },
});

const W = 640;
const H = 260;
const PAD_L = 36;
const PAD_B = 28;
const PAD_T = 12;
const CH = H - PAD_B - PAD_T;

const max = computed(() => Math.max(1, ...props.data.map((d) => d.total)));

const ticks = computed(() => {
    const m = max.value;
    const mid = Math.ceil(m / 2);
    return [0, mid, m].filter((v, i, a) => a.indexOf(v) === i);
});

const slot = computed(() => (W - PAD_L - 8) / Math.max(1, props.data.length));
const barW = computed(() => Math.max(8, slot.value * 0.55));

function y(v) {
    return PAD_T + CH - (v / max.value) * CH;
}
function x(i) {
    return PAD_L + slot.value * i + (slot.value - barW.value) / 2;
}
</script>

<template>
    <div>
        <svg v-if="max > 0" :viewBox="`0 0 ${W} ${H}`" class="h-64 w-full" role="img" aria-label="Grafik laporan per bulan">
            <g v-for="t in ticks" :key="t" class="text-muted-foreground">
                <line :x1="PAD_L" :x2="W - 4" :y1="y(t)" :y2="y(t)" stroke="currentColor" stroke-opacity="0.25" stroke-dasharray="3 3" />
                <text :x="PAD_L - 6" :y="y(t) + 4" text-anchor="end" font-size="11" fill="currentColor">{{ t }}</text>
            </g>
            <g v-for="(d, i) in data" :key="d.label">
                <rect
                    :x="x(i)"
                    :y="y(d.total)"
                    :width="barW"
                    :height="Math.max(0, PAD_T + CH - y(d.total))"
                    rx="4"
                    style="fill: hsl(var(--primary));"
                >
                    <title>{{ d.label }}: {{ d.total }} laporan</title>
                </rect>
                <text :x="x(i) + barW / 2" :y="H - 8" text-anchor="middle" font-size="11" class="fill-muted-foreground">
                    {{ d.label }}
                </text>
            </g>
        </svg>
        <p v-else class="py-16 text-center text-sm text-muted-foreground">
            Belum ada data pada tahun ini.
        </p>
    </div>
</template>
