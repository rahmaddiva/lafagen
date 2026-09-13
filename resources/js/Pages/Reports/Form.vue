<script setup>
import { computed, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, FileText, Image as ImageIcon, Info, Loader2 } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import Select from '@/components/ui/select/Select.vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import PhotoUploader from '@/components/PhotoUploader.vue';
import { useCommunity } from '@/composables/useCommunity';

const props = defineProps({
    report: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
});

const { url } = useCommunity();

const form = reactive({
    title: props.report?.title ?? '',
    category_id: props.report?.category_id ?? '',
    start_date: props.report?.start_date ?? '',
    end_date: props.report?.end_date ?? '',
    location: props.report?.location ?? '',
    description: props.report?.description ?? '',
    errors: {},
    processing: false,
});

const photosNew = reactive({ value: [] });
const photosExisting = reactive({ value: props.report?.photos ?? [] });
const photosRemoved = reactive({ value: [] });

const isEdit = !!props.report;

/** Batas ini menyalin StoreReportRequest — jaga keduanya tetap sama. */
const MAKS = { title: 150, location: 150, description: 20000 };

function sisa(nama) {
    return MAKS[nama] - (form[nama]?.length ?? 0);
}

/** Penghitung hanya ditampilkan setelah pengguna mulai menulis. */
function penghitung(nama) {
    const panjang = form[nama]?.length ?? 0;
    if (!panjang) return null;
    return { panjang, tersisa: MAKS[nama] - panjang };
}

const tanggalBalik = computed(
    () => !!form.start_date && !!form.end_date && form.end_date < form.start_date,
);

function submit() {
    form.processing = true;
    form.errors = {};

    const payload = {
        title: form.title,
        category_id: form.category_id,
        start_date: form.start_date,
        description: form.description,
        location: form.location || undefined,
        end_date: form.end_date || undefined,
        photos: photosNew.value,
        remove_photo_ids: photosRemoved.value,
    };

    const options = {
        preserveScroll: true,
        onError: (e) => { form.errors = e; },
        onFinish: () => { form.processing = false; },
    };

    if (isEdit) {
        payload._method = 'put';
        router.post(url(`reports/${props.report.id}`), payload, options);
    } else {
        router.post(url('reports'), payload, options);
    }
}

const seksi = [
    { id: 1, judul: 'Informasi kegiatan', icon: Info, deskripsi: 'Judul dan kategori laporan.' },
    { id: 2, judul: 'Waktu & lokasi', icon: CalendarDays, deskripsi: 'Kapan dan di mana kegiatan digelar.' },
    { id: 3, judul: 'Dokumentasi', icon: ImageIcon, deskripsi: 'Deskripsi kegiatan dan foto pendukung.' },
];
</script>

<template>
    <CommunityLayout :title="isEdit ? 'Ubah Laporan' : 'Tambah Laporan'">
        <PageHeader
            :title="isEdit ? 'Ubah laporan' : 'Laporan baru'"
            :subtitle="isEdit ? form.title : 'Isi tiga bagian di bawah untuk mencatat program kerja.'"
        >
            <template #actions>
                <Link
                    :href="url('reports')"
                    class="inline-flex min-h-[44px] items-center gap-1.5 rounded-md px-3 text-sm font-semibold text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                    <ArrowLeft class="h-4 w-4" aria-hidden="true" />
                    Kembali ke laporan
                </Link>
            </template>
        </PageHeader>

        <form class="mx-auto max-w-3xl space-y-4" @submit.prevent="submit">
            <!-- Seksi 1 -->
            <fieldset class="rounded-xl border bg-card p-5 shadow-card">
                <legend class="flex items-center gap-2.5 px-1 text-sm font-bold">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-extrabold text-primary-foreground tabular-nums">1</span>
                    <span class="inline-flex items-center gap-1.5">
                        <Info class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                        Informasi kegiatan
                    </span>
                </legend>

                <div class="mt-4 space-y-4">
                    <div class="space-y-1.5">
                        <div class="flex items-baseline justify-between gap-2">
                            <Label for="title">Judul kegiatan <span class="text-destructive">*</span></Label>
                            <span
                                v-if="penghitung('title')"
                                class="text-xs tabular-nums"
                                :class="penghitung('title').tersisa <= 10 ? 'font-semibold text-warning-strong' : 'text-muted-foreground'"
                            >
                                {{ penghitung('title').panjang }}/{{ MAKS.title }}
                            </span>
                        </div>
                        <Input
                            id="title"
                            v-model="form.title"
                            maxlength="150"
                            required
                            autocomplete="off"
                            aria-label="Judul kegiatan"
                            :aria-invalid="!!form.errors.title"
                            aria-describedby="title-error"
                            class="min-h-[44px]"
                        />
                        <p v-if="form.errors.title" id="title-error" class="text-sm text-destructive">{{ form.errors.title }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="category">Kategori <span class="text-destructive">*</span></Label>
                        <Select
                            id="category"
                            v-model="form.category_id"
                            required
                            aria-label="Kategori kegiatan"
                            :aria-invalid="!!form.errors.category_id"
                            class="min-h-[44px]"
                        >
                            <option value="" disabled>Pilih kategori</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </Select>
                        <p v-if="form.errors.category_id" class="text-sm text-destructive">{{ form.errors.category_id }}</p>
                        <p v-else-if="!categories.length" class="text-sm text-warning-strong">
                            Belum ada kategori. Minta admin komunitas membuat kategori lebih dulu.
                        </p>
                    </div>
                </div>
            </fieldset>

            <!-- Seksi 2 -->
            <fieldset class="rounded-xl border bg-card p-5 shadow-card">
                <legend class="flex items-center gap-2.5 px-1 text-sm font-bold">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-extrabold text-primary-foreground tabular-nums">2</span>
                    <span class="inline-flex items-center gap-1.5">
                        <CalendarDays class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                        Waktu &amp; lokasi
                    </span>
                </legend>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="start">Tanggal mulai <span class="text-destructive">*</span></Label>
                        <Input
                            id="start"
                            v-model="form.start_date"
                            type="date"
                            required
                            aria-label="Tanggal mulai"
                            :aria-invalid="!!form.errors.start_date"
                            class="min-h-[44px]"
                        />
                        <p v-if="form.errors.start_date" class="text-sm text-destructive">{{ form.errors.start_date }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="end">Tanggal selesai <span class="text-xs font-normal text-muted-foreground">(opsional)</span></Label>
                        <Input
                            id="end"
                            v-model="form.end_date"
                            type="date"
                            :min="form.start_date || undefined"
                            aria-label="Tanggal selesai"
                            :aria-invalid="!!form.errors.end_date || tanggalBalik"
                            class="min-h-[44px]"
                        />
                        <p v-if="form.errors.end_date" class="text-sm text-destructive">{{ form.errors.end_date }}</p>
                        <p v-else-if="tanggalBalik" class="text-sm text-destructive">
                            Tanggal selesai tidak boleh mendahului tanggal mulai.
                        </p>
                        <p v-else class="text-sm text-muted-foreground">
                            Kosongkan bila kegiatan hanya satu hari.
                        </p>
                    </div>
                    <div class="space-y-1.5 md:col-span-2">
                        <div class="flex items-baseline justify-between gap-2">
                            <Label for="location">Lokasi <span class="text-xs font-normal text-muted-foreground">(opsional)</span></Label>
                            <span
                                v-if="penghitung('location')"
                                class="text-xs tabular-nums"
                                :class="penghitung('location').tersisa <= 10 ? 'font-semibold text-warning-strong' : 'text-muted-foreground'"
                            >
                                {{ penghitung('location').panjang }}/{{ MAKS.location }}
                            </span>
                        </div>
                        <Input
                            id="location"
                            v-model="form.location"
                            maxlength="150"
                            placeholder="cth. Pelaihari"
                            autocomplete="off"
                            aria-label="Lokasi kegiatan"
                            :aria-invalid="!!form.errors.location"
                            class="min-h-[44px]"
                        />
                        <p v-if="form.errors.location" class="text-sm text-destructive">{{ form.errors.location }}</p>
                    </div>
                </div>
            </fieldset>

            <!-- Seksi 3 -->
            <fieldset class="rounded-xl border bg-card p-5 shadow-card">
                <legend class="flex items-center gap-2.5 px-1 text-sm font-bold">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-extrabold text-primary-foreground tabular-nums">3</span>
                    <span class="inline-flex items-center gap-1.5">
                        <ImageIcon class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                        Dokumentasi
                    </span>
                </legend>

                <div class="mt-4 space-y-5">
                    <div class="space-y-1.5">
                        <div class="flex items-baseline justify-between gap-2">
                            <Label for="desc">Deskripsi kegiatan <span class="text-destructive">*</span></Label>
                            <span
                                v-if="penghitung('description')"
                                class="text-xs tabular-nums"
                                :class="penghitung('description').tersisa <= 200 ? 'font-semibold text-warning-strong' : 'text-muted-foreground'"
                            >
                                {{ penghitung('description').panjang }}/{{ MAKS.description }}
                            </span>
                        </div>
                        <Textarea
                            id="desc"
                            v-model="form.description"
                            rows="6"
                            required
                            maxlength="20000"
                            aria-label="Deskripsi kegiatan"
                            :aria-invalid="!!form.errors.description"
                            aria-describedby="desc-petunjuk"
                        />
                        <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                        <p id="desc-petunjuk" class="text-sm text-muted-foreground">
                            Ceritakan kegiatan, hasil, dan jumlah peserta. Baris baru terjaga saat disimpan.
                        </p>
                    </div>

                    <PhotoUploader
                        v-model:existing="photosExisting.value"
                        v-model:incoming="photosNew.value"
                        v-model:removed="photosRemoved.value"
                        :errors="form.errors"
                    />
                </div>
            </fieldset>

            <!--
              Action bar lengket di mobile. Posisi bottom dikalibrasi dengan
              tinggi BottomNav (80px + safe-area) supaya bar tidak menutupi
              navigasi — keduanya harus tetap bisa dipakai.
            -->
            <div
                class="sticky bottom-[calc(5rem+env(safe-area-inset-bottom))] z-10 -mx-4 flex items-center gap-2 border-t bg-card/95 px-4 py-3 shadow-card backdrop-blur md:static md:mx-0 md:bottom-auto md:rounded-xl md:border md:px-5 md:z-0"
            >
                <Button type="submit" class="min-h-[44px] flex-1 md:flex-none" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" aria-hidden="true" />
                    <FileText v-else class="h-4 w-4" aria-hidden="true" />
                    {{ form.processing ? 'Menyimpan…' : (isEdit ? 'Simpan Perubahan' : 'Simpan Laporan') }}
                </Button>
                <Button type="button" variant="outline" class="min-h-[44px]" as-child>
                    <Link :href="url('reports')">Batal</Link>
                </Button>
                <p class="ml-auto hidden text-xs text-muted-foreground sm:block">
                    Tanda <span class="text-destructive">*</span> wajib diisi
                </p>
            </div>
        </form>
    </CommunityLayout>
</template>
