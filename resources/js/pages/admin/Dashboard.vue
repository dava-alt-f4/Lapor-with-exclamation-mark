<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/admin';

defineProps<{
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
    }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const currentUser = page.props.auth.user;

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    role: 'user',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.defaults({
        name: '',
        email: '',
        role: 'user',
    });
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (user: any) => {
    isEditing.value = true;
    editingId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        form.reset();
        form.clearErrors();
    }, 200);
};

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/users/${editingId.value}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/users', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        useForm({}).delete(`/admin/users/${id}`);
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <!-- Current Admin Info -->
        <div
            class="rounded-xl border border-sidebar-border/70 bg-sidebar p-6 shadow-sm dark:border-sidebar-border"
        >
            <h2 class="mb-4 text-lg font-semibold">Your Profile</h2>
            <div class="flex items-center gap-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-xl font-bold text-primary"
                >
                    {{ currentUser.name.charAt(0) }}
                </div>
                <div>
                    <p class="font-medium">{{ currentUser.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ currentUser.email }}
                    </p>
                </div>
                <div class="ml-auto">
                    <span
                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                    >
                        {{ currentUser.role.toUpperCase() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-sidebar shadow-sm dark:border-sidebar-border"
        >
            <div
                class="flex items-center justify-between border-b border-sidebar-border/70 p-4"
            >
                <h2 class="text-lg font-semibold">User Management</h2>
                <Button @click="openCreateModal">Add User</Button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/50 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="border-b border-sidebar-border/70 last:border-0"
                        >
                            <td class="px-6 py-4 font-medium">
                                {{ user.name }}
                                <span
                                    v-if="user.id === currentUser.id"
                                    class="ml-2 text-xs text-muted-foreground"
                                    >(You)</span
                                >
                            </td>
                            <td class="px-6 py-4">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <span
                                    :class="
                                        user.role === 'admin'
                                            ? 'font-medium text-green-600'
                                            : 'text-blue-600'
                                    "
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="space-x-2 px-6 py-4 text-right">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openEditModal(user)"
                                    >Edit</Button
                                >
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    @click="deleteUser(user.id)"
                                    :disabled="user.id === currentUser.id"
                                    >Delete</Button
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{
                    isEditing ? 'Edit User' : 'Add New User'
                }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" required />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="role">Role</Label>
                    <select
                        id="role"
                        v-model="form.role"
                        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                    <InputError :message="form.errors.role" />
                </div>

                <DialogFooter class="pt-4">
                    <Button type="button" variant="outline" @click="closeModal"
                        >Cancel</Button
                    >
                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" class="mr-2" />
                        {{
                            isEditing
                                ? form.processing
                                    ? 'Saving'
                                    : 'Save'
                                : form.processing
                                  ? 'Processing'
                                  : 'Add'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
