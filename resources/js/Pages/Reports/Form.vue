<script setup>
import { reactive } from 'vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import Select from '@/components/ui/select/Select.vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
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
</script>

<template>
    <CommunityLayout :title="isEdit ? 'Ubah Laporan' : 'Tambah Laporan'">
        <div class="mb-4">
            <Link :href="url('reports')" class="text-sm text-primary hover:underline">
                ← Kembali ke daftar laporan
            </Link>
        </div>

        <form class="mx-auto max-w-3xl space-y-4 rounded-xl border bg-card p-6" @submit.prevent="submit">
            <div class="space-y-1.5">
                <Label for="title">Judul kegiatan *</Label>
                <Input id="title" v-model="form.title" maxlength="150" required />
                <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1.5">
                    <Label for="category">Kategori *</Label>
                    <Select id="category" v-model="form.category_id" required>
                        <option value="" disabled>Pilih kategori</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </Select>
                    <p v-if="form.errors.category_id" class="text-sm text-destructive">{{ form.errors.category_id }}</p>
                </div>
                <div class="space-y-1.5">
                    <Label for="location">Lokasi</Label>
                    <Input id="location" v-model="form.location" maxlength="150" placeholder="cth. Pelaihari" />
                    <p v-if="form.errors.location" class="text-sm text-destructive">{{ form.errors.location }}</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1.5">
                    <Label for="start">Tanggal mulai *</Label>
                    <Input id="start" v-model="form.start_date" type="date" required />
                    <p v-if="form.errors.start_date" class="text-sm text-destructive">{{ form.errors.start_date }}</p>
                </div>
                <div class="space-y-1.5">
                    <Label for="end">Tanggal selesai (opsional)</Label>
                    <Input id="end" v-model="form.end_date" type="date" />
                    <p v-if="form.errors.end_date" class="text-sm text-destructive">{{ form.errors.end_date }}</p>
                </div>
            </div>

            <div class="space-y-1.5">
                <Label for="desc">Deskripsi kegiatan *</Label>
                <Textarea id="desc" v-model="form.description" rows="6" required />
                <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
            </div>

            <PhotoUploader
                v-model:existing="photosExisting.value"
                v-model:incoming="photosNew.value"
                v-model:removed="photosRemoved.value"
                :errors="form.errors"
            />

            <div class="flex gap-2 pt-2">
                <Button type="submit" :disabled="form.processing">
                    {{ isEdit ? 'Simpan Perubahan' : 'Simpan Laporan' }}
                </Button>
                <Button type="button" variant="outline" as-child>
                    <Link :href="url('reports')">Batal</Link>
                </Button>
            </div>
        </form>
    </CommunityLayout>
</template>
