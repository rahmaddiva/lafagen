<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    data: { type: Array, default: () => [] },
    /** Nomor bulan (1-12) yang sedang berjalan; null = tidak ada penanda. */
    currentMonth: { type: Number, default: null },
});

const W = 640;
const H = 280;
const PAD_L = 40;
const PAD_R = 10;
const PAD_B = 46;
const PAD_T = 22;
const CH = H - PAD_B - PAD_T;
const CW = W - PAD_L - PAD_R;

const max = computed(() => Math.max(1, ...props.data.map((d) => d.total)));

/** Sumbu Y: kelipatan tick yang enak dibaca. */
const step = computed(() => {
    const m = max.value;
    if (m <= 4) return 1;
    if (m <= 10) return 2;
    if (m <= 20) return 4;
    return Math.ceil(m / 5);
});

const ticks = computed(() => {
    const out = [];
    for (let v = 0; v <= max.value; v += step.value) out.push(v);
    if (out[out.length - 1] < max.value) out.push(out[out.length - 1] + step.value);
    return out;
});

const top = computed(() => ticks.value[ticks.value.length - 1]);

const slot = computed(() => CW / Math.max(1, props.data.length));
const barW = computed(() => Math.max(10, Math.min(38, slot.value * 0.55)));

function y(v) {
    return PAD_T + CH - (v / top.value) * CH;
}
function x(i) {
    return PAD_L + slot.value * i + (slot.value - barW.value) / 2;
}
/** Posisi tooltip dalam persen — valid karena kotak SVG sebanding viewBox. */
function tipX(i) {
    return ((x(i) + barW.value / 2) / W) * 100;
}
function tipY(v) {
    return (y(v) / H) * 100;
}

const active = ref(null);
function show(i) {
    active.value = i;
}
function hide() {
    active.value = null;
}

const isCurrent = (d) => props.currentMonth != null && d.month === props.currentMonth;
const hasData = computed(() => props.data.some((d) => d.total > 0));
</script>

<template>
    <div>
        <div v-if="hasData" class="relative">
            <div class="overflow-x-auto">
                <div class="relative" style="min-width: 520px">
                    <svg
                        :viewBox="`0 0 ${W} ${H}`"
                        class="block w-full"
                        style="height: auto"
                        role="group"
                        aria-label="Grafik batang jumlah laporan per bulan"
                    >
                        <!-- kisi + label sumbu Y -->
                        <g v-for="t in ticks" :key="`t${t}`">
                            <line
                                :x1="PAD_L"
                                :x2="W - PAD_R"
                                :y1="y(t)"
                                :y2="y(t)"
                                stroke="currentColor"
                                class="text-border"
                                stroke-dasharray="3 3"
                            />
                            <text
                                :x="PAD_L - 8"
                                :y="y(t) + 4"
                                text-anchor="end"
                                font-size="11"
                                class="fill-muted-foreground"
                            >
                                {{ t }}
                            </text>
                        </g>

                        <!-- judul sumbu Y -->
                        <text
                            :x="12"
                            :y="PAD_T + CH / 2"
                            font-size="11"
                            text-anchor="middle"
                            class="fill-muted-foreground"
                            :transform="`rotate(-90 12 ${PAD_T + CH / 2})`"
                        >
                            Jumlah laporan
                        </text>

                        <!-- garis dasar -->
                        <line
                            :x1="PAD_L"
                            :x2="W - PAD_R"
                            :y1="y(0)"
                            :y2="y(0)"
                            stroke="currentColor"
                            class="text-foreground/25"
                        />

                        <!-- batang -->
                        <g v-for="(d, i) in data" :key="d.label">
                            <rect
                                class="chart-bar animate-grow"
                                :x="x(i)"
                                :y="y(d.total)"
                                :width="barW"
                                :height="Math.max(d.total > 0 ? 2 : 0, PAD_T + CH - y(d.total))"
                                rx="4"
                                tabindex="0"
                                :style="{
                                    fill: `hsl(var(--${isCurrent(d) ? 'primary-strong' : 'primary'}))`,
                                    opacity: active === null || active === i ? 1 : 0.45,
                                    animationDelay: `${i * 22}ms`,
                                }"
                                :aria-label="`${d.label}: ${d.total} laporan`"
                                @mouseenter="show(i)"
                                @mouseleave="hide"
                                @focus="show(i)"
                                @blur="hide"
                            />

                            <!-- nilai di atas batang -->
                            <text
                                v-if="d.total > 0"
                                :x="x(i) + barW / 2"
                                :y="y(d.total) - 6"
                                text-anchor="middle"
                                font-size="11"
                                font-weight="700"
                                class="pointer-events-none fill-foreground tabular-nums"
                            >
                                {{ d.total }}
                            </text>

                            <!-- label bulan -->
                            <text
                                :x="x(i) + barW / 2"
                                :y="H - PAD_B + 18"
                                text-anchor="middle"
                                font-size="11"
                                :font-weight="isCurrent(d) ? 800 : 500"
                                class="pointer-events-none"
                                :class="isCurrent(d) ? 'fill-primary-strong' : 'fill-muted-foreground'"
                            >
                                {{ d.label }}
                            </text>

                            <!-- penanda bulan berjalan -->
                            <text
                                v-if="isCurrent(d)"
                                :x="x(i) + barW / 2"
                                :y="H - PAD_B + 32"
                                text-anchor="middle"
                                font-size="9"
                                font-weight="700"
                                class="pointer-events-none fill-primary-strong"
                            >
                                sekarang
                            </text>
                        </g>

                        <!-- judul sumbu X -->
                        <text
                            :x="PAD_L + CW / 2"
                            :y="H - 4"
                            text-anchor="middle"
                            font-size="11"
                            class="fill-muted-foreground"
                        >
                            Bulan
                        </text>
                    </svg>

                    <!-- tooltip HTML: muncul saat hover DAN fokus keyboard -->
                    <div
                        v-if="active !== null && data[active]"
                        class="pointer-events-none absolute z-10 -translate-x-1/2 -translate-y-full rounded-md border bg-popover px-2.5 py-1.5 text-xs shadow-lift"
                        :style="{ left: `${tipX(active)}%`, top: `${tipY(data[active].total) - 2}%` }"
                        role="status"
                    >
                        <span class="font-bold">{{ data[active].label }}</span>
                        <span class="text-muted-foreground"> — </span>
                        <span class="tabular-nums">{{ data[active].total }} laporan</span>
                    </div>
                </div>
            </div>

            <!-- fallback aksesibel: data lengkap dalam tabel -->
            <table class="sr-only">
                <caption>Jumlah laporan per bulan</caption>
                <thead>
                    <tr><th scope="col">Bulan</th><th scope="col">Laporan</th></tr>
                </thead>
                <tbody>
                    <tr v-for="d in data" :key="`r${d.label}`">
                        <th scope="row">{{ d.label }}</th>
                        <td class="tabular-nums">{{ d.total }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-else class="py-16 text-center text-sm text-muted-foreground">
            Belum ada laporan pada tahun ini.
        </p>
    </div>
</template>

<style scoped>
/*
 * Outline SVG: ring Tailwind tidak mempaint elemen SVG, jadi fokus keyboard
 * digambar lewat stroke.
 */
.chart-bar {
    cursor: pointer;
    outline: none;
}
.chart-bar:focus-visible {
    stroke: hsl(var(--ring));
    stroke-width: 3;
    paint-order: stroke fill;
}
</style>
