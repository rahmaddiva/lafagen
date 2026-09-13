<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { ImagePlus, Loader2, Trash2, UploadCloud, X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['change']);

const existing = defineModel('existing', { type: Array, default: () => [] });
const incoming = defineModel('incoming', { type: Array, default: () => [] });
const removed = defineModel('removed', { type: Array, default: () => [] });

/**
 * Batasan ini menyalin aturan server (StoreReportRequest):
 * photos => max:10, photos.* => image, mimes jpg|jpeg|png|webp, max:5120 KB.
 * Bila aturan server berubah, perbarui kedua tempat.
 */
const MAX_FOTO = 10;
const MAKS_UKURAN_MB = 5;
const TIPE_IJIN = ['image/jpeg', 'image/png', 'image/webp'];
const EKSTENSI_IJIN = ['jpg', 'jpeg', 'png', 'webp'];

const previews = ref([]);
const dragOver = ref(false);
const rejected = ref([]);
const inputEl = ref(null);

const total = computed(() => existing.value.length + incoming.value.length);
const sisa = computed(() => Math.max(0, MAX_FOTO - total.value));
const penuh = computed(() => sisa.value === 0);

function validate(file) {
    const masalah = [];
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    if (!TIPE_IJIN.includes(file.type) || !EKSTENSI_IJIN.includes(ext)) {
        masalah.push('format harus JPG, PNG, atau WEBP');
    }
    if (file.size > MAKS_UKURAN_MB * 1024 * 1024) {
        masalah.push(`ukuran ${MB(file.size)} > ${MAKS_UKURAN_MB} MB`);
    }
    return masalah;
}

function MB(b) {
    return (b / (1024 * 1024)).toFixed(1);
}

function addFiles(list) {
    const berkas = Array.from(list ?? []);
    const ditolak = [];
    const diterima = [];

    for (const file of berkas) {
        const masalah = validate(file);
        if (masalah.length) {
            ditolak.push({ name: file.name, alasan: masalah.join(', ') });
            continue;
        }
        if (diterima.length >= sisa.value) {
            ditolak.push({ name: file.name, alasan: `maksimal ${MAX_FOTO} foto` });
            continue;
        }
        diterima.push(file);
    }

    for (const file of diterima) {
        incoming.value.push(file);
        previews.value.push({ file, url: URL.createObjectURL(file) });
    }

    rejected.value = ditolak;
    emit('change', incoming.value);
}

function onPick(event) {
    addFiles(event.target.files);
    event.target.value = '';
}

function onDrop(event) {
    dragOver.value = false;
    addFiles(event.dataTransfer?.files);
}

function removeNew(index) {
    const [item] = previews.value.splice(index, 1);
    if (item) URL.revokeObjectURL(item.url);
    incoming.value.splice(index, 1);
    rejected.value = [];
    emit('change', incoming.value);
}

function removeExisting(photo) {
    removed.value.push(photo.id);
    existing.value = existing.value.filter((p) => p.id !== photo.id);
}

function dismissRejected() {
    rejected.value = [];
}

// Blob URL yang masih tersisa saat halaman ditinggalkan (mis. batal setelah
// pilih file) harus dilepas — tanpa ini memori bertahan sampai tab ditutup.
onBeforeUnmount(() => {
    previews.value.forEach((p) => URL.revokeObjectURL(p.url));
});
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <label class="text-sm font-medium leading-none" for="foto-kegiatan">
                Foto dokumentasi
            </label>
            <span
                :class="cn(
                    'text-xs font-semibold tabular-nums',
                    penuh ? 'text-warning-strong' : 'text-muted-foreground',
                )"
                aria-live="polite"
            >
                {{ total }}/{{ MAX_FOTO }} foto
            </span>
        </div>

        <!-- Dropzone: klik atau seret file -->
        <div
            class="relative rounded-xl border-2 border-dashed transition-colors"
            :class="dragOver ? 'border-primary bg-primary-soft' : 'border-input bg-card'"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="onDrop"
        >
            <button
                id="foto-kegiatan"
                type="button"
                class="flex min-h-[44px] w-full flex-col items-center justify-center gap-1.5 rounded-xl px-4 py-6 text-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                :aria-describedby="penuh ? 'foto-habis' : 'foto-petunjuk'"
                :disabled="penuh"
                @click="inputEl?.click()"
            >
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                    <UploadCloud v-if="!penuh" class="h-5 w-5" aria-hidden="true" />
                    <ImagePlus v-else class="h-5 w-5" aria-hidden="true" />
                </span>
                <span class="text-sm font-semibold">
                    {{ penuh ? `Kuota ${MAX_FOTO} foto penuh` : 'Seret foto ke sini atau pilih file' }}
                </span>
                <span v-if="!penuh" id="foto-petunjuk" class="text-xs text-muted-foreground">
                    JPG, PNG, atau WEBP · maksimal {{ MAKS_UKURAN_MB }} MB per foto · sisa {{ sisa }}
                </span>
                <span v-else id="foto-habis" class="text-xs text-warning-strong">
                    Hapus salah satu foto untuk menambahkan yang baru.
                </span>
            </button>

            <input
                ref="inputEl"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                multiple
                class="sr-only"
                aria-hidden="true"
                tabindex="-1"
                @change="onPick"
            >
        </div>

        <!-- Error dari server -->
        <p v-if="errors.photos" class="text-sm text-destructive">{{ errors.photos }}</p>

        <!-- File ditolak validasi klien -->
        <div
            v-if="rejected.length"
            class="rounded-lg border border-destructive/40 bg-destructive/5 p-3"
            role="alert"
        >
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-semibold text-destructive">
                    {{ rejected.length }} file tidak bisa ditambahkan
                </p>
                <button
                    type="button"
                    class="rounded p-1 text-destructive hover:bg-destructive/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    aria-label="Tutup pesan penolakan"
                    @click="dismissRejected"
                >
                    <X class="h-4 w-4" aria-hidden="true" />
                </button>
            </div>
            <ul class="mt-1.5 space-y-1 text-xs text-destructive">
                <li v-for="(r, i) in rejected" :key="`r${i}`" class="flex gap-1.5">
                    <span class="min-w-0 flex-1 truncate font-medium">{{ r.name }}</span>
                    <span class="shrink-0 opacity-80">{{ r.alasan }}</span>
                </li>
            </ul>
        </div>

        <!-- Foto tersimpan -->
        <div v-if="existing.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <figure
                v-for="photo in existing"
                :key="`old-${photo.id}`"
                class="group relative overflow-hidden rounded-lg border bg-muted"
            >
                <img :src="photo.url" :alt="photo.original_name" loading="lazy" class="aspect-video w-full object-cover">
                <figcaption class="truncate px-2 py-1 text-[11px] text-muted-foreground">
                    {{ photo.original_name }}
                </figcaption>
                <button
                    type="button"
                    class="absolute right-1.5 top-1.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-background/90 text-destructive shadow-card backdrop-blur transition-colors hover:bg-destructive hover:text-destructive-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    :aria-label="`Hapus foto tersimpan ${photo.original_name}`"
                    @click="removeExisting(photo)"
                >
                    <Trash2 class="h-4 w-4" aria-hidden="true" />
                </button>
            </figure>
        </div>

        <!-- Foto baru (belum diunggah) -->
        <div v-if="previews.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <figure
                v-for="(item, index) in previews"
                :key="item.url"
                class="group relative overflow-hidden rounded-lg border bg-muted"
            >
                <img :src="item.url" :alt="item.file.name" class="aspect-video w-full object-cover">
                <figcaption class="flex items-center gap-1 truncate px-2 py-1 text-[11px] text-muted-foreground">
                    <Loader2 class="h-3 w-3 shrink-0" aria-hidden="true" />
                    <span class="truncate">{{ item.file.name }} · {{ MB(item.file.size) }} MB</span>
                </figcaption>
                <button
                    type="button"
                    class="absolute right-1.5 top-1.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-background/90 text-destructive shadow-card backdrop-blur transition-colors hover:bg-destructive hover:text-destructive-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    :aria-label="`Buang ${item.file.name}`"
                    @click="removeNew(index)"
                >
                    <Trash2 class="h-4 w-4" aria-hidden="true" />
                </button>
            </figure>
        </div>
    </div>
</template>
