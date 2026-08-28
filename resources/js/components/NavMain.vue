<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { onUnmounted, ref, watch } from 'vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { show as adminInboxShow } from '@/routes/admin/inbox';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
const page = usePage();
const adminUnreadCount = ref(page.props.adminUnreadCount ?? 0);

const removeRouterListener = router.on('success', (event) => {
    adminUnreadCount.value = event.detail.page.props.adminUnreadCount ?? 0;
});

onUnmounted(removeRouterListener);

watch(
    () => page.props.adminUnreadCount,
    (count) => {
        adminUnreadCount.value = count ?? 0;
    },
);

useEcho<{ conversation_id: number; sender: { role: string } }>(
    'admin.inbox',
    '.MessageSent',
    (message) => {
        const activeConversationUrl = adminInboxShow(
            message.conversation_id,
        ).url;

        if (
            message.sender.role !== 'admin' &&
            page.url.split('?')[0] !== activeConversationUrl
        ) {
            adminUnreadCount.value += 1;
        }
    },
);
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <span
                            v-if="
                                item.title === 'Inbox' && adminUnreadCount > 0
                            "
                            class="ml-auto flex min-w-5 items-center justify-center rounded-full bg-destructive px-1.5 text-[10px] leading-5 text-destructive-foreground group-data-[collapsible=icon]:absolute group-data-[collapsible=icon]:-top-1 group-data-[collapsible=icon]:-right-1"
                        >
                            {{ adminUnreadCount }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
