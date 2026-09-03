<script setup lang="ts">
import { Head, router, useForm, usePage, Link } from '@inertiajs/vue3';
import { echo, useEcho } from '@laravel/echo-vue';
import { Send, User as UserIcon } from '@lucide/vue';
import { ref, nextTick, onUnmounted, watch, onMounted } from 'vue';
import { store as adminInboxStore } from '@/actions/App/Http/Controllers/Admin/InboxController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    index as adminInbox,
    show as adminInboxShow,
    markRead as adminInboxMarkRead,
} from '@/routes/admin/inbox';

const props = defineProps<{
    conversations: Array<{
        id: number;
        user: { name: string; email: string };
        messages: Array<{
            id: number;
            body: string;
            created_at: string;
            sender: { role: string };
        }>;
        unread_count: number;
    }>;
    activeConversation?: {
        id: number;
        user: { name: string };
    };
    messages?: Array<{
        id: number;
        body: string;
        sender_id: number;
        created_at: string;
        sender: { name: string; role: string; avatar_url: string | null };
    }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Inbox',
                href: adminInbox(),
            },
        ],
    },
});

const page = usePage();
const currentUser = page.props.auth.user;
const conversationsList = ref([...props.conversations]);
const messagesList = ref(props.messages ? [...props.messages] : []);
const messagesContainer = ref<HTMLElement | null>(null);

const form = useForm({
    body: '',
});

type InboxMessage = {
    id: number;
    conversation_id: number;
    body: string;
    sender_id: number;
    created_at: string;
    sender: { name: string; role: string; avatar_url: string | null };
};

const updateConversationPreview = (message: InboxMessage) => {
    const conversation = conversationsList.value.find(
        (item) => item.id === message.conversation_id,
    );

    if (!conversation) {
        return;
    }

    conversation.messages = [message];

    if (
        message.sender.role !== 'admin' &&
        props.activeConversation?.id !== message.conversation_id
    ) {
        conversation.unread_count += 1;
    }

    conversationsList.value.sort((first, second) => {
        const firstDate = first.messages[0]?.created_at ?? '';
        const secondDate = second.messages[0]?.created_at ?? '';

        return secondDate.localeCompare(firstDate);
    });
};

const markConversationAsRead = (conversationId: number) => {
    router.post(
        adminInboxMarkRead.url(conversationId),
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

useEcho<InboxMessage>('admin.inbox', '.MessageSent', (message) => {
    if (
        !conversationsList.value
            .find((conversation) => conversation.id === message.conversation_id)
            ?.messages.some((item) => item.id === message.id)
    ) {
        updateConversationPreview(message);
    }

    if (
        props.activeConversation?.id === message.conversation_id &&
        !messagesList.value.some((item) => item.id === message.id)
    ) {
        messagesList.value.push(message);
        scrollToBottom();
    }

    if (props.activeConversation?.id === message.conversation_id && message.sender.role !== 'admin') {
        markConversationAsRead(message.conversation_id);
    }
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop =
                messagesContainer.value.scrollHeight;
        }
    });
};

let subscribedConversationId: number | null = null;

const setupEchoListener = (conversationId: number) => {
    const channelName = `conversation.${conversationId}`;

    echo()
        .private(channelName)
        .listen('.MessageSent', (message: any) => {
            if (
                !messagesList.value.some((item: any) => item.id === message.id)
            ) {
                messagesList.value.push(message);
                scrollToBottom();
            }
        });
    subscribedConversationId = conversationId;
};

const teardownEchoListener = () => {
    if (subscribedConversationId !== null) {
        echo().leaveChannel(`conversation.${subscribedConversationId}`);
        subscribedConversationId = null;
    }
};

watch(
    () => props.conversations,
    (newConversations) => {
        conversationsList.value = [...newConversations];
    },
    { deep: true },
);

watch(
    () => props.messages,
    (newMessages) => {
        if (newMessages) {
            messagesList.value = [...newMessages];
            scrollToBottom();
        }
    },
    { deep: true },
);

watch(
    () => props.activeConversation?.id,
    (newId) => {
        teardownEchoListener();

        if (newId) {
            setupEchoListener(newId);
        }
    },
    { immediate: true },
);

onUnmounted(teardownEchoListener);
onMounted(scrollToBottom);

const submit = () => {
    if (!form.body.trim() || !props.activeConversation) {
        return;
    }

    form.post(adminInboxStore.url(props.activeConversation.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        },
    });
};
</script>

<template>
    <Head title="Inbox" />

    <div
        class="flex h-[calc(100vh-8rem)] overflow-hidden rounded-xl border border-sidebar-border/70 bg-sidebar shadow-sm dark:border-sidebar-border"
    >
        <!-- Sidebar: Conversation List -->
        <div class="flex w-1/3 flex-col border-r border-sidebar-border/70">
            <div class="border-b border-sidebar-border/70 p-4">
                <h2 class="text-lg font-semibold">Conversations</h2>
            </div>
            <div class="flex-1 overflow-y-auto">
                <div
                    v-if="conversations.length === 0"
                    class="p-4 text-center text-sm text-muted-foreground"
                >
                    No conversations found.
                </div>
                <Link
                    v-for="conv in conversationsList"
                    :key="conv.id"
                    :href="adminInboxShow(conv.id)"
                    :class="[
                        'flex flex-col gap-1 border-b border-sidebar-border/70 p-4 transition-colors hover:bg-muted/50',
                        activeConversation?.id === conv.id ? 'bg-muted' : '',
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">{{
                            conv.user.name
                        }}</span>
                        <div class="flex items-center gap-2">
                            <span
                                v-if="conv.messages.length > 0"
                                class="text-[10px] text-muted-foreground"
                            >
                                {{
                                    new Date(
                                        conv.messages[0].created_at,
                                    ).toLocaleDateString()
                                }}
                            </span>
                            <span
                                v-if="conv.unread_count > 0"
                                class="flex min-w-5 items-center justify-center rounded-full bg-destructive px-1.5 text-[10px] leading-5 text-destructive-foreground"
                            >
                                {{ conv.unread_count }}
                            </span>
                        </div>
                    </div>
                    <div class="truncate text-xs text-muted-foreground">
                        {{
                            conv.messages.length > 0
                                ? conv.messages[0].body
                                : 'No messages'
                        }}
                    </div>
                </Link>
            </div>
        </div>

        <!-- Main: Chat Area -->
        <div class="flex flex-1 flex-col bg-background">
            <template v-if="activeConversation">
                <!-- Chat Header -->
                <div
                    class="flex items-center gap-3 border-b border-sidebar-border/70 bg-sidebar p-4"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary"
                    >
                        <UserIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="font-semibold">
                            {{ activeConversation.user.name }}
                        </h2>
                    </div>
                </div>

                <!-- Messages Area -->
                <div
                    ref="messagesContainer"
                    class="flex-1 space-y-4 overflow-y-auto p-4"
                >
                    <div
                        v-if="messagesList.length === 0"
                        class="flex h-full items-center justify-center text-muted-foreground"
                    >
                        No messages yet.
                    </div>

                    <div
                        v-for="message in messagesList"
                        :key="message.id"
                        :class="[
                            'flex w-max max-w-[75%] flex-col gap-1 rounded-lg px-4 py-2 text-sm',
                            message.sender_id === currentUser.id
                                ? 'ml-auto bg-primary text-primary-foreground'
                                : 'bg-muted',
                        ]"
                    >
                        <div>{{ message.body }}</div>
                        <div class="mt-1 text-right text-[10px] opacity-50">
                            {{
                                new Date(message.created_at).toLocaleTimeString(
                                    [],
                                    { hour: '2-digit', minute: '2-digit' },
                                )
                            }}
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="border-t border-sidebar-border/70 bg-sidebar p-4">
                    <form @submit.prevent="submit" class="flex gap-2">
                        <Input
                            v-model="form.body"
                            placeholder="Type your reply..."
                            class="flex-1"
                            :disabled="form.processing"
                            autocomplete="off"
                        />
                        <Button
                            type="submit"
                            :disabled="form.processing || !form.body.trim()"
                        >
                            <Send class="h-4 w-4" />
                            <span class="sr-only">Send</span>
                        </Button>
                    </form>
                </div>
            </template>
            <template v-else>
                <div
                    class="flex h-full items-center justify-center text-muted-foreground"
                >
                    Select a conversation to start messaging
                </div>
            </template>
        </div>
    </div>
</template>
