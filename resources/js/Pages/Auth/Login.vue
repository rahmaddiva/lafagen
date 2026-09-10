<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
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

function submit() {
    form.transform((d) => ({ ...d, remember: form.remember ? 1 : 0 })).post(action, {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-muted/40 px-4">
        <Card class="w-full max-w-md p-8">
            <div class="mb-6 flex flex-col items-center text-center">
                <img
                    :src="community.logo"
                    :alt="community.short"
                    class="h-16 w-16 object-contain"
                    @error="$event.target.style.display = 'none'"
                >
                <h1 class="mt-3 text-2xl font-bold">{{ community.title }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ community.name }} — Kabupaten Tanah Laut
                </p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" autocomplete="username" required />
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
                    />
                    <p v-if="form.errors.password" class="text-sm text-destructive">
                        {{ form.errors.password }}
                    </p>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-input">
                    Ingat saya
                </label>
                <Button type="submit" class="w-full" :disabled="form.processing">
                    Masuk
                </Button>
                <a href="/" class="block text-center text-sm text-primary hover:underline">
                    ← Kembali pilih komunitas
                </a>
            </form>
        </Card>
    </div>
</template>
