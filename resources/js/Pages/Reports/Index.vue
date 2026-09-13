<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Download,
    Filter,
    Image as ImageIcon,
    Plus,
    RotateCcw,
    Search,
    X,
} from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Select from '@/components/ui/select/Select.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Table from '@/components/ui/table/Table.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import ReportCard from '@/components/ReportCard.vue';
import { useCommunity } from '@/composables/useCommunity';
import { formatRentang } from '@/lib/format';
import { cn } from '@/lib/utils';

const props = defineProps({
    reports: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] },
});

const { url } = useCommunity();

const form = reactive({
    month: props.filters.month ?? '',
    year: props.filters.year ?? '',
    category_id: props.filters.category_id ?? '',
    q: props.filters.q ?? '',
});

const thisYear = new Date().getFullYear();
const years = [thisYear, thisYear - 1, thisYear - 2];
const months = [
    { value: 1, label: 'Januari' }, { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' }, { value: 4, label: 'April' },
    { value: 5, label: 'Mei' }, { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' }, { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' }, { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' }, { value: 12, label: 'Desember' },
];

/** Filter terbuka di desktop; di mobile dilipat sampai dipanggil. */
const filterOpen = ref(false);

function params() {
    const out = {};
    if (form.month) out.month = form.month;
    if (form.year) out.year = form.year;
    if (form.category_id) out.category_id = form.category_id;
    if (form.q) out.q = form.q;
    return out;
}

function apply() {
    router.get(url('reports'), params(), { preserveScroll: true, replace: true });
}

function reset() {
    form.month = '';
    form.year = '';
    form.category_id = '';
    form.q = '';
    router.get(url('reports'), {}, { preserveScroll: true, replace: true });
}

/** Chip ringkas: hapus satu filter lalu langsung terapkan. */
function clearFilter(key) {
    form[key] = '';
    apply();
}

const activeChips = computed(() => {
    const chips = [];
    if (form.q) chips.push({ key: 'q', label: `Cari: "${form.q}"` });
    if (form.category_id) {
        const c = props.categories.find((x) => String(x.id) === String(form.category_id));
        chips.push({ key: 'category_id', label: `Kategori: ${c?.name ?? form.category_id}` });
    }
    if (form.month) {
        const m = months.find((x) => String(x.value) === String(form.month));
        chips.push({ key: 'month', label: `Bulan: ${m?.label ?? form.month}` });
    }
    if (form.year) chips.push({ key: 'year', label: `Tahun: ${form.year}` });
    return chips;
});

function exportHref() {
    const qs = new URLSearchParams(params()).toString();
    return url('reports/export') + (qs ? `?${qs}` : '');
}

/**
 * Label paginasi Laravel datang sebagai "« Previous" / "Next »" / angka.
 * Entitas/panah dibuang, diganti ikon Lucide — dan tiap tautan jadi <a>
 * sungguhan supaya klik tengah / Ctrl+klik membuka tab baru (§3.7).
 */
const pageLinks = computed(() =>
    (props.reports.links ?? []).map((l) => {
        const raw = String(l.label ?? '')
            .replace(/&laquo;|«/g, '')
            .replace(/&raquo;|»/g, '')
            .replace(/&amp;/g, '&')
            .trim();
        const dir = /previous|sebelum/i.test(raw) ? 'prev' : /next|berikut/i.test(raw) ? 'next' : 'num';
        return {
            url: l.url,
            active: !!l.active,
            dir,
            text: dir === 'prev' ? 'Sebelumnya' : dir === 'next' ? 'Berikutnya' : raw,
            aria: dir === 'prev' ? 'Ke halaman sebelumnya' : dir === 'next' ? 'Ke halaman berikutnya' : `Ke halaman ${raw}`,
        };
    }),
);

const showingFrom = computed(() => props.reports.from ?? 1);
const showingTo = computed(() => props.reports.to ?? props.reports.total ?? 0);
const hasFilters = computed(() => activeChips.value.length > 0);
</script>

<template>
    <CommunityLayout title="Laporan">
        <PageHeader title="Laporan Program Kerja" :subtitle="`${reports.total} laporan${hasFilters ? ' cocok dengan filter' : ''}`">
            <template #actions>
                <Button variant="outline" as-child>
                    <!-- Unduhan: anchor biasa, bukan router Inertia. -->
                    <a :href="exportHref()">
                        <Download class="h-4 w-4" aria-hidden="true" />
                        Export Excel
                    </a>
                </Button>
                <Button as-child>
                    <Link :href="url('reports/create')">
                        <Plus class="h-4 w-4" aria-hidden="true" />
                        Tambah Laporan
                    </Link>
                </Button>
            </template>
        </PageHeader>

        <!-- Filter -->
        <section class="mb-4 rounded-xl border bg-card shadow-card">
            <button
                type="button"
                class="flex min-h-[44px] w-full items-center justify-between gap-2 px-4 py-3 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring md:hidden"
                :aria-expanded="filterOpen"
                aria-controls="filter-laporan"
                @click="filterOpen = !filterOpen"
            >
                <span class="inline-flex items-center gap-2">
                    <Filter class="h-4 w-4" aria-hidden="true" />
                    Filter
                    <span v-if="activeChips.length" class="rounded-full bg-primary px-1.5 text-xs font-bold text-primary-foreground tabular-nums">
                        {{ activeChips.length }}
                    </span>
                </span>
                <ChevronRight class="h-4 w-4 transition-transform" :class="filterOpen ? 'rotate-90' : ''" aria-hidden="true" />
            </button>

            <div
                id="filter-laporan"
                :class="cn('gap-2 p-4 pt-0 md:block md:p-4', filterOpen ? 'block' : 'hidden md:block')"
            >
                <form class="grid gap-2 sm:grid-cols-2 lg:grid-cols-6" @submit.prevent="apply">
                    <Select v-model="form.month" aria-label="Saring berdasarkan bulan" class="min-h-[44px]">
                        <option value="">Semua bulan</option>
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </Select>
                    <Select v-model="form.year" aria-label="Saring berdasarkan tahun" class="min-h-[44px]">
                        <option value="">Semua tahun</option>
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </Select>
                    <Select v-model="form.category_id" aria-label="Saring berdasarkan kategori" class="min-h-[44px]">
                        <option value="">Semua kategori</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </Select>
                    <div class="relative sm:col-span-1">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                        <Input
                            v-model="form.q"
                            type="search"
                            aria-label="Cari laporan"
                            placeholder="Cari judul, lokasi…"
                            class="min-h-[44px] pl-9"
                        />
                    </div>
                    <div class="flex gap-2 sm:col-span-2 lg:col-span-2">
                        <Button type="submit" class="min-h-[44px] flex-1">Terapkan</Button>
                        <Button type="button" variant="outline" class="min-h-[44px]" @click="reset">
                            <RotateCcw class="h-4 w-4" aria-hidden="true" />
                            <span class="md:hidden lg:inline">Reset</span>
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Chip filter aktif -->
            <div v-if="activeChips.length" class="flex flex-wrap items-center gap-2 border-t px-4 py-3">
                <span class="text-xs font-medium text-muted-foreground">Aktif:</span>
                <button
                    v-for="chip in activeChips"
                    :key="chip.key"
                    type="button"
                    class="inline-flex items-center gap-1 rounded-full bg-primary-soft py-1 pl-2.5 pr-1.5 text-xs font-semibold text-primary-strong transition-colors hover:bg-primary hover:text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    :aria-label="`Hapus filter ${chip.label}`"
                    @click="clearFilter(chip.key)"
                >
                    {{ chip.label }}
                    <X class="h-3.5 w-3.5" aria-hidden="true" />
                </button>
            </div>
        </section>

        <EmptyState
            v-if="reports.data.length === 0"
            :title="hasFilters ? 'Tidak ada hasil' : 'Belum ada laporan'"
            :description="hasFilters
                ? 'Tidak ada laporan yang cocok dengan filter ini. Coba longgarkan filter atau atur ulang.'
                : 'Belum ada laporan di komunitas ini. Mulai dengan menambahkan laporan program kerja pertama.'"
        >
            <template #action>
                <Button v-if="hasFilters" variant="outline" @click="reset">
                    <RotateCcw class="h-4 w-4" aria-hidden="true" />
                    Atur ulang filter
                </Button>
                <Button v-else as-child>
                    <Link :href="url('reports/create')">
                        <Plus class="h-4 w-4" aria-hidden="true" />
                        Tambah Laporan
                    </Link>
                </Button>
            </template>
        </EmptyState>

        <template v-else>
            <p class="mb-2 text-sm text-muted-foreground">
                Menampilkan <span class="font-semibold tabular-nums text-foreground">{{ showingFrom }}–{{ showingTo }}</span>
                dari <span class="font-semibold tabular-nums text-foreground">{{ reports.total }}</span> laporan
            </p>

            <!-- Tabel: hanya ≥768px -->
            <div class="hidden min-w-0 overflow-x-auto rounded-xl border bg-card md:block">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Judul</TableHead>
                            <TableHead>Kategori</TableHead>
                            <TableHead>Tanggal</TableHead>
                            <TableHead>Lokasi</TableHead>
                            <TableHead>Pelapor</TableHead>
                            <TableHead class="text-right">
                                <span class="inline-flex items-center justify-end gap-1">
                                    <ImageIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                    Foto
                                </span>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="row in reports.data" :key="row.id">
                            <TableCell class="max-w-[22rem] font-medium">
                                <Link
                                    :href="url(`reports/${row.id}`)"
                                    class="block truncate text-primary hover:text-primary-strong hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 rounded"
                                >
                                    {{ row.title }}
                                </Link>
                            </TableCell>
                            <TableCell>
                                <Badge variant="secondary">{{ row.category?.name ?? '—' }}</Badge>
                            </TableCell>
                            <TableCell class="whitespace-nowrap tabular-nums">
                                {{ formatRentang(row.start_date, row.end_date) }}
                            </TableCell>
                            <TableCell class="max-w-[12rem] truncate">{{ row.location || 'Tanpa lokasi' }}</TableCell>
                            <TableCell>{{ row.user?.name ?? '—' }}</TableCell>
                            <TableCell class="text-right tabular-nums">{{ row.photos_count ?? 0 }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Kartu: hanya <768px -->
            <div class="grid gap-3 md:hidden">
                <ReportCard v-for="row in reports.data" :key="`c${row.id}`" :report="row" />
            </div>

            <!--
              Paginasi (§3.7): Inertia <Link> merender <a href="…"> sungguhan dan
              hanya mencegat klik kiri biasa — Ctrl+klik / klik tengah tetap
              membuka tab baru oleh browser. Tombol + router.get() sebelumnya
              membuat tautan tidak bisa dibuka di tab baru.
            -->
            <nav
                v-if="reports.last_page > 1"
                role="navigation"
                aria-label="Navigasi halaman"
                class="mt-4 flex flex-wrap items-center justify-center gap-1.5"
            >
                <template v-for="(link, i) in pageLinks" :key="`p${i}`">
                    <!-- halaman aktif / tautan mati: bukan elemen interaktif -->
                    <span
                        v-if="!link.url || link.active"
                        :class="cn(
                            'inline-flex min-h-[44px] min-w-[44px] items-center justify-center gap-1 rounded-md border px-3 text-sm',
                            link.active
                                ? 'border-transparent bg-primary font-semibold text-primary-foreground'
                                : 'border-dashed text-muted-foreground/50',
                        )"
                        :aria-current="link.active ? 'page' : undefined"
                        :aria-disabled="!link.url ? 'true' : undefined"
                    >
                        <ChevronLeft v-if="link.dir === 'prev'" class="h-4 w-4" aria-hidden="true" />
                        <span v-if="link.dir === 'num'" class="tabular-nums">{{ link.text }}</span>
                        <span v-if="link.dir !== 'num'" class="sr-only">{{ link.text }}</span>
                        <ChevronRight v-if="link.dir === 'next'" class="h-4 w-4" aria-hidden="true" />
                    </span>

                    <Link
                        v-else
                        :href="link.url"
                        :aria-label="link.aria"
                        preserve-scroll
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center gap-1 rounded-md border border-input bg-background px-3 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    >
                        <ChevronLeft v-if="link.dir === 'prev'" class="h-4 w-4" aria-hidden="true" />
                        <span v-if="link.dir === 'num'" class="tabular-nums">{{ link.text }}</span>
                        <span v-if="link.dir !== 'num'" class="sr-only">{{ link.text }}</span>
                        <ChevronRight v-if="link.dir === 'next'" class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </template>
            </nav>
        </template>
    </CommunityLayout>
</template>
