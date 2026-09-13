<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useCommunity } from '@/composables/useCommunity';

const props = defineProps({
    data: { type: Array, default: () => [] },
});

const { community } = useCommunity();

/**
 * Palet dibaca dari token --chart-1..5 agar tidak ada warna hardcoded.
 * Urutan harus persis chart-1, chart-2, ... chart-5 (lalu berulang) — ini yang
 * dibandingkan pemeriksaan §3.2.
 */
const palette = ref([]);

function readPalette() {
    const cs = getComputedStyle(document.documentElement);
    palette.value = [1, 2, 3, 4, 5].map(
        (i) => `hsl(${(cs.getPropertyValue(`--chart-${i}`) || '').trim()})`,
    );
}

onMounted(readPalette);
// Tema komunitas diubah lewat atribut data-community pada <html>; hitung ulang
// setelah DOM sempat terpengaruh (flush: 'post').
watch(() => community.value?.key, readPalette, { flush: 'post' });

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
    return props.data
        .filter((d) => d.total > 0)
        .map((d, i) => {
            const frac = d.total / t;
            const a0 = acc * 360;
            acc += frac;
            // Irisan tunggal = 360°. Busur SVG dari sebuah titik kembali ke titik
            // yang sama tidak merender apa pun, jadi sisakan celah 0,01°.
            const a1 = Math.min(acc * 360, a0 + 359.99);
            return {
                ...d,
                a0,
                a1,
                pct: Math.round(frac * 100),
                fill: palette.value[i % palette.value.length] || 'transparent',
            };
        });
});

const hovered = ref(null);
function enter(name) {
    hovered.value = name;
}
function leave() {
    hovered.value = null;
}

const centerLabel = computed(() => {
    const h = slices.value.find((s) => s.name === hovered.value);
    return h ? { big: `${h.pct}%`, small: h.name } : { big: String(total.value), small: 'laporan' };
});
</script>

<template>
    <div v-if="total > 0" class="flex flex-col items-center gap-4 sm:flex-row sm:gap-5">
        <svg
            viewBox="0 0 220 220"
            class="h-48 w-48 shrink-0 sm:h-52 sm:w-52"
            role="group"
            aria-label="Grafik donut jumlah laporan per kategori"
        >
            <path
                v-for="s in slices"
                :key="s.name"
                :data-donut-slice="s.name"
                :d="arc(110, 110, 95, 58, s.a0, s.a1)"
                :fill="s.fill"
                stroke="hsl(var(--card))"
                stroke-width="2"
                class="donut-slice transition-opacity"
                :opacity="hovered === null || hovered === s.name ? 1 : 0.4"
                tabindex="0"
                :aria-label="`${s.name}: ${s.total} laporan (${s.pct}%)`"
                @mouseenter="enter(s.name)"
                @mouseleave="leave"
                @focus="enter(s.name)"
                @blur="leave"
            >
                <title>{{ s.name }}: {{ s.total }} laporan ({{ s.pct }}%)</title>
            </path>
            <text
                x="110"
                y="106"
                text-anchor="middle"
                font-size="26"
                font-weight="800"
                class="fill-foreground tabular-nums"
            >
                {{ centerLabel.big }}
            </text>
            <text
                x="110"
                y="126"
                text-anchor="middle"
                font-size="11"
                class="fill-muted-foreground"
            >
                {{ centerLabel.small }}
            </text>
        </svg>

        <!--
          min-w-0 wajib di ul DAN li: keduanya grid item, dan default
          min-width:auto membuat nama kategori panjang menolak menyusut sehingga
          truncate tidak pernah aktif (penyebab overflow horizontal 1024px).
        -->
        <ul class="grid w-full min-w-0 flex-1 gap-2 text-sm">
            <li
                v-for="s in slices"
                :key="`l${s.name}`"
                class="flex min-w-0 items-center gap-2 rounded-md px-1.5 py-1 transition-colors"
                :class="hovered === s.name ? 'bg-muted' : ''"
                @mouseenter="enter(s.name)"
                @mouseleave="leave"
            >
                <span class="h-3 w-3 shrink-0 rounded-sm" :style="{ background: s.fill }" aria-hidden="true" />
                <span class="min-w-0 flex-1 truncate">{{ s.name }}</span>
                <span class="shrink-0 tabular-nums text-muted-foreground">{{ s.pct }}%</span>
                <span class="w-8 shrink-0 text-right font-bold tabular-nums">{{ s.total }}</span>
            </li>
        </ul>

        <table class="sr-only">
            <caption>Jumlah laporan per kategori</caption>
            <thead>
                <tr><th scope="col">Kategori</th><th scope="col">Laporan</th><th scope="col">Persen</th></tr>
            </thead>
            <tbody>
                <tr v-for="s in slices" :key="`r${s.name}`">
                    <th scope="row">{{ s.name }}</th>
                    <td class="tabular-nums">{{ s.total }}</td>
                    <td class="tabular-nums">{{ s.pct }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p v-else class="py-16 text-center text-sm text-muted-foreground">
        Belum ada laporan pada tahun ini.
    </p>
</template>

<style scoped>
/* Ring Tailwind tidak mempaint elemen SVG — fokus keyboard digambar via stroke. */
.donut-slice {
    cursor: pointer;
    outline: none;
}
.donut-slice:focus-visible {
    stroke: hsl(var(--ring));
    stroke-width: 3;
    paint-order: stroke fill;
}
</style>
