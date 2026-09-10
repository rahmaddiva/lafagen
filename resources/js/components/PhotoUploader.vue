<script setup>
import { ref } from 'vue';

defineProps({
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['change']);

const existing = defineModel('existing', { type: Array, default: () => [] });
const incoming = defineModel('incoming', { type: Array, default: () => [] });
const removed = defineModel('removed', { type: Array, default: () => [] });

const previews = ref([]);

function onPick(event) {
    const files = Array.from(event.target.files ?? []);
    files.forEach((file) => {
        incoming.value.push(file);
        previews.value.push({ file, url: URL.createObjectURL(file) });
    });
    event.target.value = '';
    emit('change', incoming.value);
}

function removeNew(index) {
    const [item] = previews.value.splice(index, 1);
    URL.revokeObjectURL(item.url);
    incoming.value.splice(index, 1);
    emit('change', incoming.value);
}

function removeExisting(photo) {
    removed.value.push(photo.id);
    existing.value = existing.value.filter((p) => p.id !== photo.id);
}
</script>

<template>
    <div class="space-y-3">
        <label class="block">
            <span class="mb-1 block text-sm font-medium">Foto kegiatan (maks 10 file, 5 MB per file)</span>
            <input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                multiple
                class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-2 file:text-sm file:font-medium file:text-primary-foreground"
                @change="onPick"
            >
        </label>
        <p v-if="errors.photos" class="text-sm text-destructive">{{ errors.photos }}</p>

        <div v-if="existing.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <figure v-for="photo in existing" :key="`old-${photo.id}`" class="relative overflow-hidden rounded-lg border">
                <img :src="photo.url" :alt="photo.original_name" class="aspect-video w-full object-cover">
                <button
                    type="button"
                    class="absolute right-1 top-1 rounded-full bg-destructive px-2 py-0.5 text-xs font-bold text-destructive-foreground"
                    @click="removeExisting(photo)"
                >
                    ✕
                </button>
            </figure>
        </div>

        <div v-if="previews.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <figure v-for="(item, index) in previews" :key="item.url" class="relative overflow-hidden rounded-lg border">
                <img :src="item.url" :alt="item.file.name" class="aspect-video w-full object-cover">
                <button
                    type="button"
                    class="absolute right-1 top-1 rounded-full bg-destructive px-2 py-0.5 text-xs font-bold text-destructive-foreground"
                    @click="removeNew(index)"
                >
                    ✕
                </button>
            </figure>
        </div>
    </div>
</template>
