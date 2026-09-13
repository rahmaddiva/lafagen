<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Pencil, Trash2, UserPlus, Users } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Select from '@/components/ui/select/Select.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Table from '@/components/ui/table/Table.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import {
    AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogOverlay, AlertDialogPortal, AlertDialogRoot, AlertDialogTitle,
    DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle,
} from 'radix-vue';
import CommunityLayout from '@/Layouts/CommunityLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import { useCommunity } from '@/composables/useCommunity';
import { inisial } from '@/lib/format';

const props = defineProps({
    users: { type: Array, default: () => [] },
    current_user_id: { type: Number, required: true },
});

const { url } = useCommunity();

const dialogOpen = ref(false);
const editing = ref(null);
const deleteTarget = ref(null);
const errors = ref({});

const form = ref({
    name: '',
    email: '',
    password: '',
    role: 'anggota',
});

const isEdit = computed(() => !!editing.value);

const roleLabel = (role) => (role === 'admin' ? 'Admin' : 'Anggota');
const roleVariant = (role) => (role === 'admin' ? 'soft' : 'secondary');

function resetForm() {
    form.value = { name: '', email: '', password: '', role: 'anggota' };
    errors.value = {};
}

/** Hapus pesan error satu field begitu pengguna mulai memperbaikinya. */
function clearError(field) {
    if (errors.value[field]) {
        const next = { ...errors.value };
        delete next[field];
        errors.value = next;
    }
}

function openCreate() {
    editing.value = null;
    resetForm();
    dialogOpen.value = true;
}

function openEdit(user) {
    editing.value = user;
    resetForm();
    form.value.name = user.name;
    form.value.email = user.email;
    form.value.role = user.role;
    dialogOpen.value = true;
}

function submit() {
    const payload = { ...form.value };
    if (editing.value && !payload.password) delete payload.password;

    const options = {
        preserveScroll: true,
        // Laravel mengirim { name: '…', email: '…', password: '…', role: '…' };
        // kunci objek = nama field, jadi errors.<field> langsung dipakai per field.
        onError: (e) => { errors.value = e; },
        onSuccess: () => { dialogOpen.value = false; resetForm(); },
    };

    if (editing.value) {
        payload._method = 'put';
        router.post(url(`users/${editing.value.id}`), payload, options);
    } else {
        router.post(url('users'), payload, options);
    }
}

function confirmDelete() {
    if (!deleteTarget.value) return;
    router.delete(url(`users/${deleteTarget.value.id}`), { preserveScroll: true });
    deleteTarget.value = null;
}
</script>

<template>
    <CommunityLayout title="Pengguna">
        <PageHeader
            title="Pengguna komunitas"
            subtitle="Kelola akun anggota dan admin komunitas ini."
        >
            <template #actions>
                <Button class="min-h-[44px] focus-visible:ring-2 focus-visible:ring-offset-2" @click="openCreate">
                    <UserPlus class="h-4 w-4" aria-hidden="true" />
                    Tambah Pengguna
                </Button>
            </template>
        </PageHeader>

        <EmptyState
            v-if="!users.length"
            title="Belum ada pengguna"
            description="Tambahkan akun pertama agar warga bisa mengirim laporan."
        >
            <template #icon>
                <Users class="h-6 w-6" aria-hidden="true" />
            </template>
            <template #action>
                <Button class="mt-1 min-h-[44px] focus-visible:ring-2 focus-visible:ring-offset-2" @click="openCreate">
                    <UserPlus class="h-4 w-4" aria-hidden="true" />
                    Tambah Pengguna
                </Button>
            </template>
        </EmptyState>

        <template v-else>
            <!-- Desktop: tabel -->
            <div class="hidden overflow-hidden rounded-xl border bg-card md:block">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Pengguna</TableHead>
                            <TableHead>Peran</TableHead>
                            <TableHead class="text-center">Laporan</TableHead>
                            <TableHead>Bergabung</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="u in users" :key="u.id">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-bold text-primary-strong"
                                        aria-hidden="true"
                                    >
                                        {{ inisial(u.name) }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold">
                                            {{ u.name }}
                                            <span v-if="u.id === current_user_id" class="text-xs font-normal text-muted-foreground">(Anda)</span>
                                        </span>
                                        <span class="block truncate text-xs text-muted-foreground">{{ u.email }}</span>
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="roleVariant(u.role)">{{ roleLabel(u.role) }}</Badge>
                            </TableCell>
                            <TableCell class="text-center tabular-nums">{{ u.reports_count }}</TableCell>
                            <!-- created_at sudah diformat server ("13 Sep 2026") — tampilkan apa adanya. -->
                            <TableCell class="whitespace-nowrap tabular-nums text-muted-foreground">{{ u.created_at }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="focus-visible:ring-2 focus-visible:ring-offset-2"
                                        :aria-label="`Ubah ${u.name}`"
                                        @click="openEdit(u)"
                                    >
                                        <Pencil class="h-4 w-4" aria-hidden="true" />
                                    </Button>
                                    <!-- Admin tidak bisa menghapus akunnya sendiri; tombol disembunyikan, bukan dinonaktifkan. -->
                                    <Button
                                        v-if="u.id !== current_user_id"
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:bg-destructive/10 hover:text-destructive focus-visible:ring-2 focus-visible:ring-offset-2"
                                        :aria-label="`Hapus ${u.name}`"
                                        @click="deleteTarget = u"
                                    >
                                        <Trash2 class="h-4 w-4" aria-hidden="true" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Mobile: kartu -->
            <ul class="space-y-3 md:hidden">
                <li
                    v-for="u in users"
                    :key="u.id"
                    class="rounded-xl border bg-card p-4 shadow-sm"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-bold text-primary-strong"
                            aria-hidden="true"
                        >
                            {{ inisial(u.name) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold">
                                {{ u.name }}
                                <span v-if="u.id === current_user_id" class="text-xs font-normal text-muted-foreground">(Anda)</span>
                            </p>
                            <p class="truncate text-xs text-muted-foreground">{{ u.email }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <Badge :variant="roleVariant(u.role)">{{ roleLabel(u.role) }}</Badge>
                                <span class="text-xs text-muted-foreground">
                                    {{ u.reports_count }} laporan · bergabung {{ u.created_at }}
                                </span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-11 w-11 focus-visible:ring-2 focus-visible:ring-offset-2"
                                :aria-label="`Ubah ${u.name}`"
                                @click="openEdit(u)"
                            >
                                <Pencil class="h-5 w-5" aria-hidden="true" />
                            </Button>
                            <Button
                                v-if="u.id !== current_user_id"
                                variant="ghost"
                                size="icon"
                                class="h-11 w-11 text-destructive hover:bg-destructive/10 hover:text-destructive focus-visible:ring-2 focus-visible:ring-offset-2"
                                :aria-label="`Hapus ${u.name}`"
                                @click="deleteTarget = u"
                            >
                                <Trash2 class="h-5 w-5" aria-hidden="true" />
                            </Button>
                        </div>
                    </div>
                </li>
            </ul>
        </template>

        <!-- Dialog tambah/ubah -->
        <DialogRoot v-model:open="dialogOpen">
            <DialogPortal>
                <DialogOverlay class="fixed inset-0 z-50 bg-black/50" />
                <DialogContent class="fixed left-1/2 top-1/2 z-50 max-h-[90vh] w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-xl border bg-card p-6 shadow-lg">
                    <DialogTitle class="text-lg font-bold">
                        {{ isEdit ? 'Ubah pengguna' : 'Tambah pengguna' }}
                    </DialogTitle>
                    <DialogDescription class="mt-1 text-sm text-muted-foreground">
                        Akun otomatis masuk ke komunitas Anda.
                    </DialogDescription>

                    <form class="mt-4 space-y-4" @submit.prevent="submit">
                        <div class="space-y-1.5">
                            <Label for="u-name">Nama</Label>
                            <Input
                                id="u-name"
                                v-model="form.name"
                                maxlength="150"
                                autocomplete="name"
                                required
                                :aria-invalid="!!errors.name"
                                :aria-describedby="errors.name ? 'u-name-error' : undefined"
                                @input="clearError('name')"
                            />
                            <p v-if="errors.name" id="u-name-error" class="text-sm text-destructive">
                                {{ errors.name }}
                            </p>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="u-email">Email</Label>
                            <Input
                                id="u-email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                :aria-invalid="!!errors.email"
                                :aria-describedby="errors.email ? 'u-email-error' : undefined"
                                @input="clearError('email')"
                            />
                            <p v-if="errors.email" id="u-email-error" class="text-sm text-destructive">
                                {{ errors.email }}
                            </p>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="u-password">Password</Label>
                            <p v-if="isEdit" id="u-password-hint" class="text-xs text-muted-foreground">
                                Kosongkan jika tidak ingin mengubah password.
                            </p>
                            <Input
                                id="u-password"
                                v-model="form.password"
                                type="password"
                                :autocomplete="isEdit ? 'off' : 'new-password'"
                                :required="!isEdit"
                                :aria-invalid="!!errors.password"
                                :aria-describedby="errors.password ? 'u-password-error' : (isEdit ? 'u-password-hint' : undefined)"
                                @input="clearError('password')"
                            />
                            <p v-if="errors.password" id="u-password-error" class="text-sm text-destructive">
                                {{ errors.password }}
                            </p>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="u-role">Peran</Label>
                            <Select
                                id="u-role"
                                v-model="form.role"
                                :aria-invalid="!!errors.role"
                                :aria-describedby="errors.role ? 'u-role-error' : undefined"
                                @change="clearError('role')"
                            >
                                <option value="anggota">Anggota</option>
                                <option value="admin">Admin</option>
                            </Select>
                            <p v-if="errors.role" id="u-role-error" class="text-sm text-destructive">
                                {{ errors.role }}
                            </p>
                        </div>

                        <div class="mt-5 flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                class="min-h-[44px] focus-visible:ring-2 focus-visible:ring-offset-2"
                                @click="dialogOpen = false"
                            >
                                Batal
                            </Button>
                            <Button
                                type="submit"
                                class="min-h-[44px] focus-visible:ring-2 focus-visible:ring-offset-2"
                            >
                                Simpan
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </DialogPortal>
        </DialogRoot>

        <!-- Konfirmasi hapus -->
        <AlertDialogRoot :open="!!deleteTarget" @update:open="(v) => !v && (deleteTarget = null)">
            <AlertDialogPortal>
                <AlertDialogOverlay class="fixed inset-0 z-50 bg-black/50" />
                <AlertDialogContent class="fixed left-1/2 top-1/2 z-50 w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-6 shadow-lg">
                    <AlertDialogTitle class="text-lg font-bold">
                        Hapus akun “{{ deleteTarget?.name }}”?
                    </AlertDialogTitle>
                    <AlertDialogDescription class="mt-1 text-sm text-muted-foreground">
                        Akun “{{ deleteTarget?.name }}” dan seluruh laporan yang pernah dibuatnya akan dihapus permanen.
                        <span v-if="deleteTarget?.role === 'admin'">
                            Komunitas harus selalu punya minimal satu admin — menghapus admin terakhir akan ditolak sistem.
                        </span>
                    </AlertDialogDescription>
                    <div class="mt-5 flex justify-end gap-2">
                        <AlertDialogCancel as-child>
                            <Button variant="outline" class="min-h-[44px] focus-visible:ring-2 focus-visible:ring-offset-2">
                                Batal
                            </Button>
                        </AlertDialogCancel>
                        <AlertDialogAction as-child>
                            <Button variant="destructive" class="min-h-[44px] focus-visible:ring-2 focus-visible:ring-offset-2" @click="confirmDelete">
                                Hapus
                            </Button>
                        </AlertDialogAction>
                    </div>
                </AlertDialogContent>
            </AlertDialogPortal>
        </AlertDialogRoot>
    </CommunityLayout>
</template>
