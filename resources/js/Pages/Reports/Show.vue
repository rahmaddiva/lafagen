<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogOverlay,
    AlertDialogPortal,
    AlertDialogRoot,
    AlertDialogTrigger,
    DialogContent,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
} from 'radix-vue';
import {
    ArrowLeft,
    CalendarRange,
    ChevronLeft,
    ChevronRight,
    ImageOff,
    MapPin,
    User,
    X,
} from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Card from '@/components/ui/card/Card.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { useCommunity } from '@/composables/useCommunity';
import { formatRentang, inisial } from '@/lib/format';

const props = defineProps({
    report: { type: Object, required: true },
    editable: { type: Boolean, default: false },
});

const { url } = useCommunity();
const lightbox = ref(null);
const confirmOpen = ref(false);

const photos = computed(() => props.report.photos ?? []);

const tanggal = computed(() => formatRentang(props.report.start_date, props.report.end_date));

/** Lokasi kosong ditampilkan eksplisit, bukan tanda hubung tanpa konteks. */
const lokasi = computed(() => props.report.location?.trim() || 'Tanpa lokasi');

const lightboxIndex = computed(() =>
    lightbox.value ? photos.value.findIndex((p) => p.id === lightbox.value.id) : -1,
);

function geserLightbox(arah) {
    if (!lightbox.value || photos.value.length < 2) return;
    const i = lightboxIndex.value;
    const n = (i + arah + photos.value.length) % photos.value.length;
    lightbox.value = photos.value[n];
}

function destroy() {
    confirmOpen.value = false;
    router.delete(url(`reports/${props.report.id}`), { preserveScroll: true });
}
</script>

<template>
    <CommunityLayout :title="report.title">
        <Link
            :href="url('reports')"
            class="mb-4 inline-flex min-h-[44px] items-center gap-1.5 rounded-lg text-sm font-semibold text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
        >
            <ArrowLeft class="h-4 w-4" aria-hidden="true" />
            Kembali ke laporan
        </Link>

        <!-- Hero: judul + aksi, lalu baris meta. Heading halaman tetap satu, milik CommunityLayout. -->
        <PageHeader :title="report.title">
            <template v-if="editable" #actions>
                <Button variant="outline" as-child>
                    <Link :href="url(`reports/${report.id}/edit`)">Ubah</Link>
                </Button>
                <AlertDialogRoot v-model:open="confirmOpen">
                    <AlertDialogTrigger as-child>
                        <Button variant="destructive">Hapus</Button>
                    </AlertDialogTrigger>
                    <AlertDialogPortal>
                        <AlertDialogOverlay class="fixed inset-0 z-50 bg-black/50" />
                        <AlertDialogContent
                            class="fixed left-1/2 top-1/2 z-50 w-full max-w-sm -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-6 shadow-lg"
                        >
                            <AlertDialogTitle class="text-lg font-bold">Hapus laporan?</AlertDialogTitle>
                            <AlertDialogDescription class="mt-1 text-sm text-muted-foreground">
                                Laporan “{{ report.title }}” beserta fotonya akan dihapus permanen.
                            </AlertDialogDescription>
                            <div class="mt-4 flex justify-end gap-2">
                                <AlertDialogCancel as-child>
                                    <Button variant="outline">Batal</Button>
                                </AlertDialogCancel>
                                <AlertDialogAction as-child>
                                    <Button variant="destructive" @click="destroy">Ya, hapus</Button>
                                </AlertDialogAction>
                            </div>
                        </AlertDialogContent>
                    </AlertDialogPortal>
                </AlertDialogRoot>
            </template>
        </PageHeader>

        <div class="-mt-4 mb-6 flex flex-wrap items-center gap-x-4 gap-y-2">
            <Badge v-if="report.category?.name" variant="soft">{{ report.category.name }}</Badge>
            <p class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                <span class="inline-flex items-center gap-1.5">
                    <CalendarRange class="h-4 w-4 shrink-0" aria-hidden="true" />
                    {{ tanggal }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <MapPin class="h-4 w-4 shrink-0" aria-hidden="true" />
                    {{ lokasi }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <User class="h-4 w-4 shrink-0" aria-hidden="true" />
                    {{ report.user?.name ?? 'Tanpa pelapor' }}
                </span>
            </p>
        </div>

        <!-- Dua kolom di desktop: konten kiri, kartu meta kanan yang lengket. -->
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0 space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base font-bold">Foto dokumentasi</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="photos.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <button
                                v-for="photo in photos"
                                :key="photo.id"
                                type="button"
                                class="group overflow-hidden rounded-lg border focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                @click="lightbox = photo"
                            >
                                <img
                                    :src="photo.url"
                                    :alt="photo.original_name"
                                    loading="lazy"
                                    class="aspect-video w-full object-cover transition-transform duration-200 group-hover:scale-[1.03]"
                                >
                            </button>
                        </div>
                        <div v-else class="flex items-center gap-2 rounded-lg bg-muted/60 px-4 py-3 text-sm text-muted-foreground">
                            <ImageOff class="h-5 w-5 shrink-0" aria-hidden="true" />
                            Belum ada foto dokumentasi
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base font-bold">Deskripsi laporan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p v-if="report.description" class="whitespace-pre-line leading-relaxed text-foreground/90">
                            {{ report.description }}
                        </p>
                        <p v-else class="text-sm text-muted-foreground">
                            Tidak ada deskripsi untuk laporan ini.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <aside class="min-w-0 space-y-4 lg:sticky lg:top-20 lg:self-start">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base font-bold">Informasi laporan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="space-y-4 text-sm">
                            <div v-if="report.category?.name">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Kategori</dt>
                                <dd class="mt-1">
                                    <Badge variant="soft">{{ report.category.name }}</Badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Tanggal kejadian</dt>
                                <dd class="mt-1 flex items-center gap-1.5 font-medium">{{ tanggal }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Lokasi</dt>
                                <dd class="mt-1 flex items-start gap-1.5 font-medium">
                                    <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" aria-hidden="true" />
                                    <span class="min-w-0 break-words">{{ lokasi }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Pelapor</dt>
                                <dd class="mt-1 flex items-center gap-2">
                                    <span
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground"
                                        aria-hidden="true"
                                    >
                                        {{ inisial(report.user?.name) }}
                                    </span>
                                    <span class="min-w-0 truncate font-medium">{{ report.user?.name ?? '—' }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Dokumentasi</dt>
                                <dd class="mt-1 font-medium">{{ photos.length }} foto</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </aside>
        </div>

        <DialogRoot :open="!!lightbox" @update:open="(v) => !v && (lightbox = null)">
            <DialogPortal>
                <DialogOverlay class="fixed inset-0 z-50 bg-black/80" @click="lightbox = null" />
                <DialogContent
                    class="fixed left-1/2 top-1/2 z-50 w-[calc(100%-2rem)] max-w-3xl -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-2 shadow-lg"
                >
                    <template v-if="lightbox">
                        <div class="relative">
                            <img
                                :src="lightbox.url"
                                :alt="lightbox.original_name"
                                class="max-h-[75vh] w-full rounded-lg object-contain"
                            >
                            <Button
                                variant="secondary"
                                size="icon"
                                class="absolute right-2 top-2 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                aria-label="Tutup foto"
                                @click="lightbox = null"
                            >
                                <X class="h-5 w-5" aria-hidden="true" />
                            </Button>
                            <template v-if="photos.length > 1">
                                <Button
                                    variant="secondary"
                                    size="icon"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    aria-label="Foto sebelumnya"
                                    @click="geserLightbox(-1)"
                                >
                                    <ChevronLeft class="h-5 w-5" aria-hidden="true" />
                                </Button>
                                <Button
                                    variant="secondary"
                                    size="icon"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    aria-label="Foto berikutnya"
                                    @click="geserLightbox(1)"
                                >
                                    <ChevronRight class="h-5 w-5" aria-hidden="true" />
                                </Button>
                            </template>
                        </div>
                        <p class="flex items-center justify-between px-2 py-1.5 text-xs text-muted-foreground">
                            <span class="min-w-0 truncate">{{ lightbox.original_name }}</span>
                            <span v-if="photos.length > 1" class="shrink-0 tabular-nums">
                                {{ lightboxIndex + 1 }} / {{ photos.length }}
                            </span>
                        </p>
                    </template>
                </DialogContent>
            </DialogPortal>
        </DialogRoot>
    </CommunityLayout>
</template>
