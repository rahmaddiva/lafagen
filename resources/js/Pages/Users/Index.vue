<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
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
import { useCommunity } from '@/composables/useCommunity';

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

function resetForm() {
    form.value = { name: '', email: '', password: '', role: 'anggota' };
    errors.value = {};
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
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold">Pengguna komunitas</h2>
            <Button @click="openCreate">Tambah Pengguna</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nama</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Role</TableHead>
                    <TableHead>Laporan</TableHead>
                    <TableHead>Bergabung</TableHead>
                    <TableHead class="text-right">Aksi</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="u in users" :key="u.id">
                    <TableCell class="font-medium">
                        {{ u.name }}
                        <span v-if="u.id === current_user_id" class="text-xs text-muted-foreground">(Anda)</span>
                    </TableCell>
                    <TableCell>{{ u.email }}</TableCell>
                    <TableCell>
                        <Badge :variant="u.role === 'admin' ? 'default' : 'secondary'">
                            {{ u.role === 'admin' ? 'Admin' : 'Anggota' }}
                        </Badge>
                    </TableCell>
                    <TableCell>{{ u.reports_count }}</TableCell>
                    <TableCell class="whitespace-nowrap">{{ u.created_at }}</TableCell>
                    <TableCell class="text-right">
                        <div class="flex justify-end gap-2">
                            <Button size="sm" variant="outline" @click="openEdit(u)">Ubah</Button>
                            <Button
                                v-if="u.id !== current_user_id"
                                size="sm"
                                variant="destructive"
                                @click="deleteTarget = u"
                            >
                                Hapus
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <DialogRoot v-model:open="dialogOpen">
            <DialogPortal>
                <DialogOverlay class="fixed inset-0 z-50 bg-black/50" />
                <DialogContent class="fixed left-1/2 top-1/2 z-50 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-6 shadow-lg">
                    <DialogTitle class="text-lg font-bold">
                        {{ editing ? 'Ubah pengguna' : 'Tambah pengguna' }}
                    </DialogTitle>
                    <DialogDescription class="mt-1 text-sm text-muted-foreground">
                        Akun otomatis masuk ke komunitas Anda.
                    </DialogDescription>

                    <div class="mt-4 space-y-3">
                        <div class="space-y-1.5">
                            <Label for="u-name">Nama</Label>
                            <Input id="u-name" v-model="form.name" maxlength="150" />
                            <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="u-email">Email</Label>
                            <Input id="u-email" v-model="form.email" type="email" />
                            <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="u-password">
                                Password {{ editing ? '(kosongkan bila tidak diganti)' : '' }}
                            </Label>
                            <Input id="u-password" v-model="form.password" type="password" autocomplete="new-password" />
                            <p v-if="errors.password" class="text-sm text-destructive">{{ errors.password }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="u-role">Role</Label>
                            <Select id="u-role" v-model="form.role">
                                <option value="anggota">Anggota</option>
                                <option value="admin">Admin</option>
                            </Select>
                            <p v-if="errors.role" class="text-sm text-destructive">{{ errors.role }}</p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <Button variant="outline" @click="dialogOpen = false">Batal</Button>
                        <Button @click="submit">Simpan</Button>
                    </div>
                </DialogContent>
            </DialogPortal>
        </DialogRoot>

        <AlertDialogRoot :open="!!deleteTarget" @update:open="(v) => !v && (deleteTarget = null)">
            <AlertDialogPortal>
                <AlertDialogOverlay class="fixed inset-0 z-50 bg-black/50" />
                <AlertDialogContent class="fixed left-1/2 top-1/2 z-50 w-full max-w-sm -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-6 shadow-lg">
                    <AlertDialogTitle class="text-lg font-bold">Hapus pengguna?</AlertDialogTitle>
                    <AlertDialogDescription class="mt-1 text-sm text-muted-foreground">
                        Akun “{{ deleteTarget?.name }}” akan dihapus. Laporan yang pernah dibuatnya juga terhapus.
                    </AlertDialogDescription>
                    <div class="mt-4 flex justify-end gap-2">
                        <AlertDialogCancel as-child>
                            <Button variant="outline">Batal</Button>
                        </AlertDialogCancel>
                        <AlertDialogAction as-child>
                            <Button variant="destructive" @click="confirmDelete">Hapus</Button>
                        </AlertDialogAction>
                    </div>
                </AlertDialogContent>
            </AlertDialogPortal>
        </AlertDialogRoot>
    </CommunityLayout>
</template>
