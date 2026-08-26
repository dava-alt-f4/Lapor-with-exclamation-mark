<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Component } from 'vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import {
    getMainNavItems,
    getNavigationHomeHref,
} from '@/lib/navigation';
import type { BreadcrumbItem, NavItem, NavigationContext } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    mainNavItems?: NavItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    mainNavItems: () => [],
});

const page = usePage();
const role = computed(() => page.props.auth.user.role);
const pageName = computed(() => page.component);

const context = computed<NavigationContext>(() => {
    if (pageName.value.startsWith('admin/')) {
        return 'admin';
    }

    if (pageName.value.startsWith('settings/') && role.value === 'admin') {
        return 'admin';
    }

    return 'user';
});

const usesSidebar = computed(
    () => pageName.value.startsWith('admin/') || context.value === 'admin',
);
const layoutComponent = computed<Component>(() =>
    usesSidebar.value ? AppSidebarLayout : AppHeaderLayout,
);
const mainNavItems = computed(() =>
    getMainNavItems(context.value, props.mainNavItems),
);
const homeHref = computed(() => getNavigationHomeHref(context.value));
</script>

<template>
    <component
        :is="layoutComponent"
        :breadcrumbs="props.breadcrumbs"
        :main-nav-items="mainNavItems"
        :home-href="homeHref"
    >
        <slot />
    </component>
</template>
