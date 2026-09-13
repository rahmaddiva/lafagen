<script setup>
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <header class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <!--
              <p> bukan <h1>: CommunityLayout sudah merender <h1> judul halaman
              di header lengket. Dua <h1> dalam satu dokumen merusak struktur
              heading (ditemukan saat verifikasi Dashboard).
            -->
            <p v-if="title" class="text-2xl font-extrabold tracking-tight md:text-3xl">
                {{ title }}
            </p>
            <p v-if="subtitle" class="mt-1 text-sm text-muted-foreground">
                {{ subtitle }}
            </p>
        </div>

        <!--
          Target sentuh 44px hanya di ponsel (max-md): Button berukuran default
          36px (h-9), di bawah ambang sentuh. Di desktop kerapatan dipertahankan.
          min-height mengalahkan h-9 karena tinggi terpakai = max(height, min-height).
        -->
        <div
            v-if="$slots.actions"
            class="flex shrink-0 flex-wrap items-center gap-2 max-md:[&_a]:min-h-[44px] max-md:[&_button]:min-h-[44px]"
        >
            <slot name="actions" />
        </div>
    </header>
</template>
