<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    mainNavItems?: NavItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    mainNavItems: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar :main-nav-items="props.mainNavItems" />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <Toaster rich-colors position="top-right" />
    </AppShell>
</template>
