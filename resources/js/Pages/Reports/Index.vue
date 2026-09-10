<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
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
import EmptyState from '@/Components/EmptyState.vue';
import { useCommunity } from '@/composables/useCommunity';

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

function apply() {
    const params = {};
    if (form.month) params.month = form.month;
    if (form.year) params.year = form.year;
    if (form.category_id) params.category_id = form.category_id;
    if (form.q) params.q = form.q;
    router.get(url('reports'), params, { preserveScroll: true, replace: true });
}

function reset() {
    form.month = '';
    form.year = '';
    form.category_id = '';
    form.q = '';
    router.get(url('reports'), {}, { preserveScroll: true, replace: true });
}

function exportHref() {
    const params = new URLSearchParams();
    if (form.month) params.set('month', form.month);
    if (form.year) params.set('year', form.year);
    if (form.category_id) params.set('category_id', form.category_id);
    if (form.q) params.set('q', form.q);
    const qs = params.toString();
    return url('reports/export') + (qs ? `?${qs}` : '');
}

function formatDate(value) {
    if (!value) return '—';
    const d = new Date(value);
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function range(row) {
    if (!row.end_date || row.end_date === row.start_date) return formatDate(row.start_date);
    return `${formatDate(row.start_date)} – ${formatDate(row.end_date)}`;
}
</script>

<template>
    <CommunityLayout title="Laporan">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold">Laporan Program Kerja</h2>
                <p class="text-sm text-muted-foreground">
                    Total {{ reports.total }} laporan
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <a :href="exportHref()">Export Excel</a>
                </Button>
                <Button as-child>
                    <Link :href="url('reports/create')">Tambah Laporan</Link>
                </Button>
            </div>
        </div>

        <form class="mb-4 grid grid-cols-2 gap-2 rounded-xl border bg-card p-3 md:grid-cols-5" @submit.prevent="apply">
            <Select v-model="form.month">
                <option value="">Semua bulan</option>
                <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
            </Select>
            <Select v-model="form.year">
                <option value="">Semua tahun</option>
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </Select>
            <Select v-model="form.category_id">
                <option value="">Semua kategori</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </Select>
            <Input v-model="form.q" placeholder="Cari judul, lokasi…" />
            <div class="col-span-2 flex gap-2 md:col-span-1">
                <Button type="submit" class="flex-1">Terapkan</Button>
                <Button type="button" variant="outline" @click="reset">Reset</Button>
            </div>
        </form>

        <EmptyState
            v-if="reports.data.length === 0"
            title="Belum ada laporan"
            description="Belum ada laporan yang cocok dengan filter. Tambah laporan pertama komunitas Anda."
            action-label="Tambah Laporan"
            :action-href="url('reports/create')"
        />

        <template v-else>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Judul</TableHead>
                        <TableHead>Kategori</TableHead>
                        <TableHead>Tanggal</TableHead>
                        <TableHead>Lokasi</TableHead>
                        <TableHead>Pelapor</TableHead>
                        <TableHead>Foto</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="row in reports.data" :key="row.id">
                        <TableCell class="font-medium">
                            <Link :href="url(`reports/${row.id}`)" class="text-primary hover:underline">
                                {{ row.title }}
                            </Link>
                        </TableCell>
                        <TableCell>
                            <Badge variant="secondary">{{ row.category?.name }}</Badge>
                        </TableCell>
                        <TableCell class="whitespace-nowrap">{{ range(row) }}</TableCell>
                        <TableCell>{{ row.location ?? '—' }}</TableCell>
                        <TableCell>{{ row.user?.name }}</TableCell>
                        <TableCell>{{ row.photos_count ?? 0 }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div v-if="reports.last_page > 1" class="mt-4 flex flex-wrap gap-2">
                <Button
                    v-for="link in reports.links"
                    :key="link.label"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    :disabled="!link.url"
                    @click="link.url && router.get(link.url, {}, { preserveScroll: true })"
                    v-html="link.label"
                />
            </div>
        </template>
    </CommunityLayout>
</template>
