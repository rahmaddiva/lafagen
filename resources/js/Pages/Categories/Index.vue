<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
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
    categories: { type: Array, default: () => [] },
});

const { url } = useCommunity();

const dialogOpen = ref(false);
const editing = ref(null);
const name = ref('');
const deleteTarget = ref(null);

function openCreate() {
    editing.value = null;
    name.value = '';
    dialogOpen.value = true;
}

function openEdit(category) {
    editing.value = category;
    name.value = category.name;
    dialogOpen.value = true;
}

function submit() {
    const payload = { name: name.value };
    if (editing.value) {
        payload._method = 'put';
        router.post(url(`categories/${editing.value.id}`), payload, { preserveScroll: true, onSuccess: () => { dialogOpen.value = false; } });
    } else {
        router.post(url('categories'), payload, { preserveScroll: true, onSuccess: () => { dialogOpen.value = false; name.value = ''; } });
    }
}

function confirmDelete() {
    if (!deleteTarget.value) return;
    router.delete(url(`categories/${deleteTarget.value.id}`), { preserveScroll: true });
    deleteTarget.value = null;
}
</script>

<template>
    <CommunityLayout title="Kategori">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold">Kategori laporan</h2>
            <Button @click="openCreate">Tambah Kategori</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nama</TableHead>
                    <TableHead>Jumlah laporan</TableHead>
                    <TableHead class="text-right">Aksi</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="c in categories" :key="c.id">
                    <TableCell class="font-medium">{{ c.name }}</TableCell>
                    <TableCell>{{ c.reports_count }}</TableCell>
                    <TableCell class="text-right">
                        <div class="flex justify-end gap-2">
                            <Button size="sm" variant="outline" @click="openEdit(c)">Ubah</Button>
                            <Button size="sm" variant="destructive" @click="deleteTarget = c">Hapus</Button>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <DialogRoot v-model:open="dialogOpen">
            <DialogPortal>
                <DialogOverlay class="fixed inset-0 z-50 bg-black/50" />
                <DialogContent class="fixed left-1/2 top-1/2 z-50 w-full max-w-sm -translate-x-1/2 -translate-y-1/2 rounded-xl border bg-card p-6 shadow-lg">
                    <DialogTitle class="text-lg font-bold">
                        {{ editing ? 'Ubah kategori' : 'Tambah kategori' }}
                    </DialogTitle>
                    <DialogDescription class="mt-1 text-sm text-muted-foreground">
                        Nama kategori unik dalam komunitas ini.
                    </DialogDescription>
                    <div class="mt-2 space-y-1.5">
                        <Label for="cat-name">Nama</Label>
                        <Input id="cat-name" v-model="name" maxlength="100" @keydown.enter="submit" />
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
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
                    <AlertDialogTitle class="text-lg font-bold">Hapus kategori?</AlertDialogTitle>
                    <AlertDialogDescription class="mt-1 text-sm text-muted-foreground">
                        Kategori “{{ deleteTarget?.name }}” akan dihapus. Kategori yang masih dipakai laporan tidak bisa dihapus.
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