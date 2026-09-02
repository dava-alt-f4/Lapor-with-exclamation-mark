<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    mainNavItems?: NavItem[];
    homeHref?: NavItem['href'];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    mainNavItems: () => [],
    homeHref: undefined,
});
</script>

<template>
    <AppShell variant="header">
        <AppHeader
            :main-nav-items="props.mainNavItems"
            :breadcrumbs="breadcrumbs"
            :home-href="props.homeHref"
        />
        <AppContent variant="header">
            <slot />
        </AppContent>
        <Toaster rich-colors position="top-right" />
    </AppShell>
</template>
