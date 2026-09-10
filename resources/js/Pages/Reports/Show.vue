<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogOverlay,
    AlertDialogPortal,
    AlertDialogRoot,
    AlertDialogTitle,
    AlertDialogTrigger,
    DialogContent,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
} from 'radix-vue';
import Button from '@/components/ui/button/Button.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
import { useCommunity } from '@/composables/useCommunity';

const props = defineProps({
    report: { type: Object, required: true },
    editable: { type: Boolean, default: false },
});

const { url } = useCommunity();
const lightbox = ref(null);
const confirmOpen = ref(false);

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function destroy() {
    confirmOpen.value = false;
    router.delete(url(`reports/${props.report.id}`), { preserveScroll: true });
}
</script>

<template>
    <CommunityLayout :title="report.title">
        <div class="mb-4 flex items-center justify-between gap-2">
            <Link :href="url('reports')" class="text-sm text-primary hover:underline">
                ← Kembali ke daftar laporan
            </Link>
            <div v-if="editable" class="flex gap-2">
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
            </div>
        </div>

        <article class="space-y-4 rounded-xl border bg-card p-6">
            <div class="flex flex-wrap items-center gap-2">
                <Badge variant="secondary">{{ report.category?.name }}</Badge>
                <span class="text-sm text-muted-foreground">
                    {{ formatDate(report.start_date) }}
                    <template v-if="report.end_date && report.end_date !== report.start_date">
                        – {{ formatDate(report.end_date) }}
                    </template>
                    · {{ report.location ?? 'Lokasi tidak dicantumkan' }}
                    · oleh {{ report.user?.name }}
                </span>
            </div>
            <h2 class="text-2xl font-bold">{{ report.title }}</h2>
            <p class="whitespace-pre-line leading-relaxed text-foreground/90">
                {{ report.description }}
            </p>

            <div v-if="report.photos?.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <button
                    v-for="photo in report.photos"
                    :key="photo.id"
                    type="button"
                    class="overflow-hidden rounded-lg border"
                    @click="lightbox = photo"
                >
                    <img :src="photo.url" :alt="photo.original_name" class="aspect-video w-full object-cover">
                </button>
            </div>
            <p v-else class="text-sm text-muted-foreground">
                Tidak ada foto dokumentasi.
            </p>
        </article>

        <DialogRoot :open="!!lightbox" @update:open="(v) => !v && (lightbox = null)">
            <DialogPortal>
                <DialogOverlay class="fixed inset-0 z-50 bg-black/70" @click="lightbox = null" />
                <DialogContent
                    class="fixed left-1/2 top-1/2 z-50 w-[calc(100%-2rem)] max-w-3xl -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-2 shadow-lg"
                >
                    <img
                        v-if="lightbox"
                        :src="lightbox.url"
                        :alt="lightbox.original_name"
                        class="w-full rounded-lg object-contain"
                    >
                </DialogContent>
            </DialogPortal>
        </DialogRoot>
    </CommunityLayout>
</template>
