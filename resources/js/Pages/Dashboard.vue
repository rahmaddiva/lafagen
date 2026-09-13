<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    CalendarCheck,
    Moon,
    Sunrise,
    Sun,
    Sunset,
    Users,
} from 'lucide-vue-next';
import Card from '@/components/ui/card/Card.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
import StatCard from '@/components/StatCard.vue';
import ActivityPanel from '@/components/ActivityPanel.vue';
import MonthlyBar from '@/components/charts/MonthlyBar.vue';
import CategoryDonut from '@/components/charts/CategoryDonut.vue';
import EmptyState from '@/components/EmptyState.vue';
import { useCommunity } from '@/composables/useCommunity';
import { cn } from '@/lib/utils';

const props = defineProps({
    stats: { type: Object, required: true },
    activity: { type: Object, default: () => ({ today: 0, week: 0, this_month: 0, streak: 0 }) },
    monthly: { type: Array, default: () => [] },
    byCategory: { type: Array, default: () => [] },
    recent: { type: Array, default: () => [] },
    year: { type: Number, required: true },
});

const { url, community } = useCommunity();
const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});

const thisYear = new Date().getFullYear();
const years = [thisYear, thisYear - 1, thisYear - 2];

const now = new Date();
const currentMonth = now.getMonth() + 1;

/** Sapaan + tanggal memakai locale id-ID agar konsisten dengan backend. */
const greeting = computed(() => {
    const h = now.getHours();
    if (h < 11) return { text: 'Selamat pagi', icon: Sunrise };
    if (h < 15) return { text: 'Selamat siang', icon: Sun };
    if (h < 19) return { text: 'Selamat sore', icon: Sunset };
    return { text: 'Selamat malam', icon: Moon };
});

const todayLabel = computed(() =>
    now.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }),
);

const firstName = computed(() => (user.value.name || 'Rekan').trim().split(/\s+/)[0]);

function changeYear(value) {
    router.get(url('dashboard'), { year: Number(value) }, { preserveScroll: true, replace: true });
}

function formatTanggal(value) {
    if (!value) return '';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <CommunityLayout title="Dashboard">
        <!-- Hero sapaan -->
        <section class="animate-rise overflow-hidden rounded-2xl border bg-card shadow-card">
            <div class="flex flex-wrap items-start justify-between gap-4 p-5 md:p-6">
                <div class="flex min-w-0 items-start gap-3.5">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary-strong"
                        aria-hidden="true"
                    >
                        <component :is="greeting.icon" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xl font-extrabold tracking-tight md:text-2xl">
                            {{ greeting.text }}, {{ firstName }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ todayLabel }}</p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-3 py-1 text-xs font-bold uppercase tracking-wide text-primary-strong"
                    >
                        <img
                            v-if="community.logo"
                            :src="community.logo"
                            :alt="''"
                            class="h-4 w-4 rounded-full object-contain"
                        />
                        {{ community.short }}
                    </span>
                    <Link
                        :href="url('reports/create')"
                        class="inline-flex items-center gap-1.5 rounded-full bg-primary px-3.5 py-1.5 text-xs font-bold text-primary-foreground shadow-card transition-colors hover:bg-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    >
                        Buat laporan
                        <ArrowRight class="h-3.5 w-3.5" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Pemilih tahun: segmented control -->
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-semibold text-muted-foreground">Ringkasan</h2>
            <div
                role="group"
                aria-label="Pilih tahun"
                class="inline-flex rounded-lg border bg-muted p-0.5"
            >
                <button
                    v-for="y in years"
                    :key="y"
                    type="button"
                    :aria-pressed="y === year"
                    :class="
                        cn(
                            'min-h-[36px] rounded-md px-3.5 text-sm font-semibold tabular-nums transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                            y === year
                                ? 'bg-card text-foreground shadow-card'
                                : 'text-muted-foreground hover:text-foreground',
                        )
                    "
                    @click="changeYear(y)"
                >
                    {{ y }}
                </button>
            </div>
        </div>

        <!-- 4 stat card -->
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                :value="stats.total_reports"
                label="Total laporan"
                :icon="BarChart3"
                tone="primary"
                hint="sepanjang waktu"
            />
            <StatCard
                :value="stats.total_members"
                label="Jumlah anggota"
                :icon="Users"
                tone="info"
                hint="di komunitas ini"
            />
            <StatCard
                :value="stats.this_month"
                label="Laporan bulan ini"
                :icon="CalendarCheck"
                tone="success"
                :delta="{ current: stats.this_month, previous: stats.prev_month }"
                hint="vs bulan lalu"
            />
            <StatCard
                :value="stats.this_year"
                :label="`Laporan ${year}`"
                :icon="BarChart3"
                tone="warning"
                hint="tanggal mulai dalam tahun ini"
            />
        </div>

        <!-- Chart -->
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <Card class="min-w-0">
                <CardHeader>
                    <CardTitle>Laporan per bulan</CardTitle>
                    <CardDescription>{{ year }} · berdasarkan tanggal mulai kegiatan</CardDescription>
                </CardHeader>
                <CardContent>
                    <MonthlyBar :data="monthly" :current-month="year === thisYear ? currentMonth : null" />
                </CardContent>
            </Card>
            <Card class="min-w-0">
                <CardHeader>
                    <CardTitle>Kategori paling aktif</CardTitle>
                    <CardDescription>{{ year }} · {{ byCategory.filter((c) => c.total > 0).length }} kategori terpakai</CardDescription>
                </CardHeader>
                <CardContent>
                    <CategoryDonut :data="byCategory" />
                </CardContent>
            </Card>
        </div>

        <!-- Aktivitas -->
        <div class="mt-4">
            <ActivityPanel :activity="activity" />
        </div>

        <!-- 5 laporan terbaru: blok terpisah, baris teks sederhana -->
        <Card class="mt-4">
            <CardHeader class="flex-row items-center justify-between space-y-0">
                <div>
                    <CardTitle>5 laporan terbaru</CardTitle>
                    <CardDescription>Diurutkan dari tanggal mulai terbaru</CardDescription>
                </div>
                <Link
                    :href="url('reports')"
                    class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 rounded"
                >
                    Lihat semua
                    <ArrowRight class="h-4 w-4" aria-hidden="true" />
                </Link>
            </CardHeader>
            <CardContent v-if="recent.length" class="pt-1">
                <ul class="divide-y">
                    <li v-for="r in recent" :key="r.id">
                        <Link
                            :href="url(`reports/${r.id}`)"
                            class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5 rounded-md px-1 py-2.5 transition-colors hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <span class="min-w-0 flex-1 truncate font-semibold">{{ r.title }}</span>
                            <span class="shrink-0 text-xs text-muted-foreground">
                                {{ r.category_name }} · {{ formatTanggal(r.start_date) }} · {{ r.user_name }}
                            </span>
                            <span class="w-full shrink-0 text-xs text-muted-foreground/80">
                                dikirim {{ r.created_ago }}
                            </span>
                        </Link>
                    </li>
                </ul>
            </CardContent>
            <CardContent v-else>
                <EmptyState
                    title="Belum ada laporan"
                    :description="`Tidak ada laporan dengan tanggal mulai pada ${year}.`"
                    action-label="Buat laporan pertama"
                    :action-href="url('reports/create')"
                />
            </CardContent>
        </Card>
    </CommunityLayout>
</template>
