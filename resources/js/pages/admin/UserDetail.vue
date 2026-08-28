<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes/admin';
import { useInitials } from '@/composables/useInitials';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
        avatar_url?: string;
        country?: string;
        province?: string;
        city?: string;
        district?: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin Dashboard',
                href: dashboard(),
            },
            {
                title: 'User Detail',
                href: '#',
            },
        ],
    },
});

const { getInitials } = useInitials();
</script>

<template>
    <Head :title="`${user.name} - User Detail`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    User Detail
                </h1>
                <p class="text-sm text-muted-foreground">
                    View account information and address details.
                </p>
            </div>
            <Button variant="outline" as-child>
                <Link :href="dashboard()">
                    <ArrowLeft class="h-4 w-4" />
                    <span>Back</span>
                </Link>
            </Button>
        </div>

        <div
            class="flex flex-col gap-8 rounded-xl border border-sidebar-border/70 bg-sidebar p-6 shadow-sm dark:border-sidebar-border lg:flex-row"
        >
            <div
                class="flex shrink-0 flex-col items-center gap-4 border-b border-sidebar-border/70 pb-8 lg:w-56 lg:border-r lg:border-b-0 lg:pr-8 lg:pb-0"
            >
                <Avatar class="size-36">
                    <AvatarImage
                        v-if="user.avatar_url"
                        :src="user.avatar_url"
                        :alt="user.name"
                    />
                    <AvatarFallback class="text-6xl font-semibold">
                        {{ getInitials(user.name) }}
                    </AvatarFallback>
                </Avatar>
                <div class="text-center">
                    <p class="font-semibold">{{ user.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ user.email }}
                    </p>
                </div>
                <span
                    class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary"
                >
                    {{ user.role.toUpperCase() }}
                </span>
            </div>

            <div class="grid flex-1 gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="user-name">Name</Label>
                    <Input id="user-name" v-model="user.name" readonly />
                </div>
                <div class="space-y-2">
                    <Label for="user-email">Email</Label>
                    <Input id="user-email" v-model="user.email" readonly />
                </div>
                <div class="space-y-2">
                    <Label for="user-country">Country</Label>
                    <Input
                        id="user-country"
                        v-model="user.country"
                        readonly
                    />
                </div>
                <div class="space-y-2">
                    <Label for="user-province">Province</Label>
                    <Input
                        id="user-province"
                        v-model="user.province"
                        readonly
                    />
                </div>
                <div class="space-y-2">
                    <Label for="user-city">City</Label>
                    <Input
                        id="user-city"
                        v-model="user.city"
                        readonly
                    />
                </div>
                <div class="space-y-2">
                    <Label for="user-district">District</Label>
                    <Input
                        id="user-district"
                        v-model="user.district"
                        readonly
                    />
                </div>
            </div>
        </div>
    </div>
</template>
