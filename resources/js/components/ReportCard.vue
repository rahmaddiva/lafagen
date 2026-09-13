<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Camera, MapPin, User } from 'lucide-vue-next';
import Badge from '@/components/ui/badge/Badge.vue';
import { formatRentang } from '@/lib/format';
import { useCommunity } from '@/composables/useCommunity';

const props = defineProps({
    report: { type: Object, required: true },
});

const { url } = useCommunity();
const href = computed(() => url(`reports/${props.report.id}`));
</script>

<template>
    <!--
      Kartu untuk tampilan <md. Satu-satunya sumber data tetap `reports.data`;
      tabel desktop memakai baris yang sama.
    -->
    <Link
        :href="href"
        class="block min-w-0 rounded-xl border bg-card p-4 shadow-card transition-colors hover:bg-muted/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
    >
        <div class="flex items-start justify-between gap-2">
            <Badge variant="soft" class="shrink-0">{{ report.category?.name ?? 'Tanpa kategori' }}</Badge>
            <span
                v-if="report.photos_count"
                class="inline-flex shrink-0 items-center gap-1 text-xs font-medium text-muted-foreground"
            >
                <Camera class="h-3.5 w-3.5" aria-hidden="true" />
                <span class="tabular-nums">{{ report.photos_count }}</span>
            </span>
        </div>

        <h3 class="mt-2 min-w-0 break-words text-base font-bold leading-snug">
            {{ report.title }}
        </h3>

        <p class="mt-1.5 text-sm tabular-nums text-muted-foreground">
            {{ formatRentang(report.start_date, report.end_date) }}
        </p>

        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground">
            <span class="inline-flex min-w-0 items-center gap-1">
                <MapPin class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                <span class="truncate">{{ report.location || 'Tanpa lokasi' }}</span>
            </span>
            <span class="inline-flex min-w-0 items-center gap-1">
                <User class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                <span class="truncate">{{ report.user?.name ?? '—' }}</span>
            </span>
        </div>
    </Link>
</template>
