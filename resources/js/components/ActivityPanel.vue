<script setup>
import { CalendarCheck, CalendarDays, CalendarRange, Flame } from 'lucide-vue-next';

defineProps({
    activity: {
        type: Object,
        default: () => ({ today: 0, week: 0, this_month: 0, streak: 0 }),
    },
});

const items = [
    { key: 'today', label: 'Hari ini', icon: CalendarCheck, tone: 'bg-primary-soft text-primary-strong' },
    { key: 'week', label: 'Minggu ini', icon: CalendarDays, tone: 'bg-info-soft text-info-strong' },
    { key: 'this_month', label: 'Bulan ini', icon: CalendarRange, tone: 'bg-success-soft text-success-strong' },
];
</script>

<template>
    <!--
      Hanya angka aktivitas + streak. Daftar "laporan terbaru" sengaja TIDAK
      ada di sini — blok itu berdiri sendiri di Dashboard (spec §6/§7.3) supaya
      tidak dirender dua kali.
    -->
    <div class="rounded-xl border bg-card p-4 shadow-card">
        <h3 class="text-sm font-bold tracking-tight text-foreground">Aktivitas pelaporan</h3>
        <p class="mt-0.5 text-xs text-muted-foreground">Berdasarkan tanggal mulai kegiatan.</p>

        <dl class="mt-3 grid grid-cols-3 gap-2">
            <div
                v-for="it in items"
                :key="it.key"
                class="rounded-lg border bg-background p-2.5"
            >
                <dt class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <span :class="['flex h-6 w-6 items-center justify-center rounded-md', it.tone]">
                        <component :is="it.icon" class="h-3.5 w-3.5" aria-hidden="true" />
                    </span>
                    <span class="truncate">{{ it.label }}</span>
                </dt>
                <dd class="mt-1.5 text-2xl font-extrabold tabular-nums leading-none">
                    {{ activity[it.key] ?? 0 }}
                </dd>
            </div>
        </dl>

        <div class="mt-3 flex items-center gap-2.5 rounded-lg bg-warning-soft px-3 py-2.5">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-warning text-warning-foreground">
                <Flame class="h-4 w-4" aria-hidden="true" />
            </span>
            <div class="min-w-0">
                <p class="text-sm font-bold leading-tight text-warning-strong tabular-nums">
                    {{ activity.streak ?? 0 }} bulan beruntun
                </p>
                <p class="text-xs text-warning-strong/80">
                    {{ (activity.streak ?? 0) > 0
                        ? 'Rentetan aktif — pertahankan konsistensi ini.'
                        : 'Belum ada rentetan — mulai dengan satu laporan bulan ini.' }}
                </p>
            </div>
        </div>
    </div>
</template>
