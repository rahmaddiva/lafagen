<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-200">
        <div class="mx-auto max-w-4xl px-6 py-20 text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-800">
                Lafagen
            </h1>
            <p class="mt-2 text-lg text-slate-500">
                Sistem Laporan Program Kerja Komunitas — Kabupaten Tanah Laut
            </p>
            <div class="mt-12 grid gap-6 sm:grid-cols-2">
                <Link
                    href="/fad/login"
                    class="group rounded-2xl border bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-teal-100 text-2xl font-black text-teal-700">
                        FAD
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-slate-800">
                        Forum Anak Daerah
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Masuk sebagai anggota FAD Tanah Laut
                    </p>
                </Link>
                <Link
                    href="/genre/login"
                    class="group rounded-2xl border bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sky-100 text-2xl font-black text-sky-700">
                        GENRE
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-slate-800">
                        Generasi Berencana
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Masuk sebagai anggota GENRE Tanah Laut
                    </p>
                </Link>
            </div>
        </div>
    </div>
</template>
