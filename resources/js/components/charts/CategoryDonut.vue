<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Array, default: () => [] },
});

const palette = [
    'hsl(var(--primary))',
    'hsl(var(--chart-2))',
    'hsl(var(--chart-3))',
    'hsl(var(--chart-4))',
    'hsl(var(--chart-5))',
    '#65a30d',
    '#ca8a04',
    '#dc2626',
];

const total = computed(() => props.data.reduce((s, d) => s + d.total, 0));

function polar(cx, cy, r, angleDeg) {
    const a = ((angleDeg - 90) * Math.PI) / 180;
    return [cx + r * Math.cos(a), cy + r * Math.sin(a)];
}

function arc(cx, cy, rOuter, rInner, a0, a1) {
    const [x0, y0] = polar(cx, cy, rOuter, a1);
    const [x1, y1] = polar(cx, cy, rOuter, a0);
    const [x2, y2] = polar(cx, cy, rInner, a0);
    const [x3, y3] = polar(cx, cy, rInner, a1);
    const large = a1 - a0 > 180 ? 1 : 0;
    return `M ${x0} ${y0} A ${rOuter} ${rOuter} 0 ${large} 0 ${x1} ${y1} L ${x2} ${y2} A ${rInner} ${rInner} 0 ${large} 1 ${x3} ${y3} Z`;
}

const slices = computed(() => {
    const t = total.value || 1;
    let acc = 0;
    return props.data.map((d, i) => {
        const frac = d.total / t;
        const a0 = acc * 360;
        acc += frac;
        const a1 = acc * 360;
        return { ...d, a0, a1, fill: palette[i % palette.length] };
    });
});
</script>

<template>
    <div v-if="total > 0" class="flex flex-col items-center gap-3 sm:flex-row">
        <svg viewBox="0 0 220 220" class="h-52 w-52 shrink-0" role="img" aria-label="Grafik laporan per kategori">
            <g v-for="s in slices" :key="s.name">
                <path v-if="s.total > 0" :d="arc(110, 110, 95, 55, s.a0, s.a1)" :fill="s.fill">
                    <title>{{ s.name }}: {{ s.total }} laporan</title>
                </path>
            </g>
            <text x="110" y="105" text-anchor="middle" font-size="26" font-weight="800" class="fill-foreground">
                {{ total }}
            </text>
            <text x="110" y="125" text-anchor="middle" font-size="12" class="fill-muted-foreground">
                laporan
            </text>
        </svg>
        <ul class="grid flex-1 gap-1.5 text-sm">
            <li v-for="s in slices" :key="s.name" class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded-sm" :style="{ background: s.fill }" />
                <span class="flex-1">{{ s.name }}</span>
                <span class="font-semibold">{{ s.total }}</span>
            </li>
        </ul>
    </div>
    <p v-else class="py-16 text-center text-sm text-muted-foreground">
        Belum ada data pada tahun ini.
    </p>
</template>
