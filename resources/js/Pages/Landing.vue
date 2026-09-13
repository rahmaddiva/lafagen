<script setup>
import { Link } from '@inertiajs/vue3';
import { Users, FileText, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    communities: { type: Array, default: () => [] },
});

// Tagline diturunkan dari `title` config ("FAD Tanah Laut").
const tagline = (c) => `Masuk sebagai anggota ${c.title}`;
</script>

<template>
    <div
        class="relative min-h-screen overflow-hidden bg-background"
    >
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-primary-soft to-transparent"
            aria-hidden="true"
        />

        <div class="relative mx-auto max-w-4xl px-6 py-16 text-center sm:py-24">
            <span
                class="animate-rise inline-flex items-center rounded-full bg-primary px-3 py-1 text-xs font-bold uppercase tracking-wider text-primary-foreground"
            >
                Kabupaten Tanah Laut
            </span>

            <h1
                class="animate-rise mt-5 text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl"
            >
                Lafagen
            </h1>
            <p class="animate-rise mt-3 text-base text-muted-foreground sm:text-lg">
                Sistem Laporan Program Kerja Komunitas
            </p>

            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                <Link
                    v-for="(c, i) in communities"
                    :key="c.key"
                    :href="`/${c.key}/login`"
                    class="animate-rise group flex flex-col items-center rounded-2xl border bg-card p-8 text-center shadow-card transition duration-200 hover:-translate-y-1 hover:shadow-lift focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    :style="{ animationDelay: `${i * 90}ms` }"
                >
                    <img
                        :src="c.logo"
                        :alt="`Logo ${c.name}`"
                        loading="lazy"
                        class="h-20 w-20 rounded-2xl bg-white object-contain p-1 ring-1 ring-border"
                    />

                    <h2 class="mt-4 text-xl font-bold text-foreground">{{ c.name }}</h2>
                    <p class="mt-1 text-sm text-muted-foreground">{{ tagline(c) }}</p>

                    <dl class="mt-5 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-1.5">
                            <Users class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                            <dt class="sr-only">Anggota</dt>
                            <dd class="font-semibold tabular-nums">
                                {{ c.members }}
                                <span class="font-normal text-muted-foreground">anggota</span>
                            </dd>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <FileText class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                            <dt class="sr-only">Laporan</dt>
                            <dd class="font-semibold tabular-nums">
                                {{ c.reports }}
                                <span class="font-normal text-muted-foreground">laporan</span>
                            </dd>
                        </div>
                    </dl>

                    <span
                        class="mt-6 inline-flex items-center gap-1.5 text-sm font-bold text-primary"
                    >
                        Masuk
                        <ArrowRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-1"
                            aria-hidden="true"
                        />
                    </span>
                </Link>
            </div>
        </div>
    </div>
</template>
