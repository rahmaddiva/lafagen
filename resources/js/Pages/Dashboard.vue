<script setup>
import { Link, router } from '@inertiajs/vue3';
import Card from '@/components/ui/card/Card.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import Select from '@/components/ui/select/Select.vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
import StatCard from '@/components/StatCard.vue';
import MonthlyBar from '@/components/charts/MonthlyBar.vue';
import CategoryDonut from '@/components/charts/CategoryDonut.vue';
import { useCommunity } from '@/composables/useCommunity';

const props = defineProps({
    stats: { type: Object, required: true },
    monthly: { type: Array, default: () => [] },
    byCategory: { type: Array, default: () => [] },
    recent: { type: Array, default: () => [] },
    year: { type: Number, required: true },
});

const { url } = useCommunity();
const thisYear = new Date().getFullYear();
const years = [thisYear + 1 > thisYear ? thisYear : thisYear, thisYear - 1, thisYear - 2].filter((v, i, a) => a.indexOf(v) === i);

function changeYear(value) {
    router.get(url('dashboard'), { year: Number(value) }, { preserveScroll: true, replace: true });
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <CommunityLayout title="Dashboard">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold">Dashboard</h2>
            <Select :model-value="String(year)" class="w-36" @update:model-value="changeYear">
                <option v-for="y in years" :key="y" :value="String(y)">{{ y }}</option>
            </Select>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard :value="stats.total_reports" label="Total laporan" />
            <StatCard :value="stats.total_members" label="Jumlah anggota" />
            <StatCard :value="stats.this_month" label="Laporan bulan ini" />
            <StatCard :value="stats.this_year" :label="`Laporan tahun ${year}`" />
        </div>

        <div class="mt-4 grid gap-3 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Laporan per bulan ({{ year }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <MonthlyBar :data="monthly" />
                </CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle>Laporan per kategori ({{ year }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <CategoryDonut :data="byCategory" />
                </CardContent>
            </Card>
        </div>

        <Card class="mt-4">
            <CardHeader>
                <CardTitle>5 laporan terbaru</CardTitle>
            </CardHeader>
            <CardContent>
                <ul v-if="recent.length" class="divide-y">
                    <li v-for="r in recent" :key="r.id" class="flex items-center justify-between gap-3 py-2">
                        <div class="min-w-0">
                            <Link :href="url(`reports/${r.id}`)" class="truncate font-medium text-primary hover:underline">
                                {{ r.title }}
                            </Link>
                            <p class="text-xs text-muted-foreground">
                                {{ r.category_name }} · {{ formatDate(r.start_date) }} · {{ r.user_name }}
                            </p>
                        </div>
                    </li>
                </ul>
                <p v-else class="text-sm text-muted-foreground">
                    Belum ada laporan pada tahun {{ year }}.
                </p>
            </CardContent>
        </Card>
    </CommunityLayout>
</template>
