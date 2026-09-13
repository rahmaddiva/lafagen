<script setup>
import { computed, nextTick, onMounted, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, LogIn, ShieldCheck } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

defineProps({ title: String });

const page = usePage();
const community = page.props.community;
const action = `/${community.key}/login`;

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// `ref` pada komponen <Input> memberi instance komponen, bukan elemen DOM,
// jadi .focus() atasnya tidak melakukan apa-apa — ambil lewat id.
onMounted(() => document.getElementById('email')?.focus());

// Login yang gagal tidak me-mount ulang komponen ini — hanya `form.errors`
// yang berubah — sehingga onMounted di atas tidak cukup. Fokus dipindah ke
// field keliru pertama supaya pengguna keyboard tidak kehilangan posisi.
watch(
    () => JSON.stringify(form.errors),
    () => {
        const pertama = ['email', 'password'].find((k) => form.errors[k]);
        if (pertama) nextTick(() => document.getElementById(pertama)?.focus());
    },
);

/** Ringkasan error di atas form (spec §7.2); error per field tetap tampil. */
const errorList = computed(() => Object.values(form.errors).filter(Boolean));

function submit() {
    form.transform((d) => ({ ...d, remember: form.remember ? 1 : 0 })).post(action, {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-muted/40 px-4 py-8">
        <div
            class="w-full max-w-4xl overflow-hidden rounded-2xl border bg-card shadow-lift lg:grid lg:grid-cols-[1.05fr_1fr]"
        >
            <!--
              Satu elemen identitas untuk kedua lebar: header gradient di ponsel,
              panel kiri di desktop. Sengaja satu elemen — kalau dipecah menjadi
              dua blok (satu lg:hidden, satu hidden lg:flex) hanya satu yang
              punya <h1> terlihat, sehingga dokumen kehilangan heading utama.
            -->
            <div
                class="flex flex-col gap-4 bg-gradient-to-br from-primary to-primary-strong p-6 text-primary-foreground lg:justify-between lg:gap-8 lg:p-10"
            >
                <div class="flex items-center gap-3">
                    <img
                        :src="community.logo"
                        :alt="community.short"
                        class="h-11 w-11 rounded-xl bg-white/15 object-contain p-1.5 lg:h-12 lg:w-12"
                        @error="$event.target.style.display = 'none'"
                    >
                    <span class="text-sm font-bold uppercase tracking-widest opacity-90">
                        {{ community.short }}
                    </span>
                </div>

                <div>
                    <h1 class="text-xl font-extrabold leading-tight tracking-tight lg:text-3xl">
                        {{ community.title }}
                    </h1>
                    <p class="mt-1 text-xs opacity-90 lg:mt-2 lg:text-sm">
                        {{ community.name }} — Kabupaten Tanah Laut
                    </p>
                    <p class="mt-6 hidden max-w-sm text-sm leading-relaxed opacity-80 lg:block">
                        Catat program kerja, unggah dokumentasi, dan pantau capaian
                        komunitas dalam satu tempat.
                    </p>
                </div>

                <p class="hidden items-center gap-2 text-xs opacity-80 lg:flex">
                    <ShieldCheck class="h-4 w-4" aria-hidden="true" />
                    Data komunitas lain tidak dapat diakses dari akun ini.
                </p>
            </div>

            <!-- Panel form -->
            <div class="p-6 sm:p-8 lg:p-10">
                <h2 class="text-2xl font-extrabold tracking-tight">Masuk</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gunakan akun yang diberikan admin komunitas Anda.
                </p>

                <!-- Ringkasan error -->
                <div
                    v-if="errorList.length"
                    role="alert"
                    class="mt-5 rounded-lg border border-destructive/40 bg-destructive/5 p-3.5"
                >
                    <p class="text-sm font-bold text-destructive">
                        {{ errorList.length === 1 ? 'Ada satu hal yang perlu diperbaiki:' : 'Ada beberapa hal yang perlu diperbaiki:' }}
                    </p>
                    <ul class="mt-1.5 list-disc space-y-0.5 pl-5 text-sm text-destructive">
                        <li v-for="(msg, i) in errorList" :key="i">{{ msg }}</li>
                    </ul>
                </div>

                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <div class="space-y-1.5">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
                            required
                            aria-label="Email"
                            :aria-invalid="!!form.errors.email"
                            class="min-h-[44px]"
                            :class="form.errors.email ? 'border-destructive' : ''"
                        />
                        <p v-if="form.errors.email" class="text-sm text-destructive">
                            {{ form.errors.email }}
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            aria-label="Password"
                            :aria-invalid="!!form.errors.password"
                            class="min-h-[44px]"
                            :class="form.errors.password ? 'border-destructive' : ''"
                        />
                        <p v-if="form.errors.password" class="text-sm text-destructive">
                            {{ form.errors.password }}
                        </p>
                    </div>
                    <label
                        class="flex min-h-[44px] cursor-pointer items-center gap-2.5 text-sm font-medium"
                    >
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="h-5 w-5 shrink-0 rounded border-input focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                        Ingat saya
                    </label>
                    <Button type="submit" class="min-h-[44px] w-full" :disabled="form.processing">
                        <LogIn class="h-4 w-4" aria-hidden="true" />
                        {{ form.processing ? 'Memproses…' : 'Masuk' }}
                    </Button>
                </form>

                <a
                    href="/"
                    class="mt-4 inline-flex min-h-[44px] w-full items-center justify-center gap-1.5 rounded-md text-sm font-semibold text-primary transition-colors hover:text-primary-strong focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                    <ArrowLeft class="h-4 w-4" aria-hidden="true" />
                    Kembali pilih komunitas
                </a>
            </div>
        </div>
    </div>
</template>
