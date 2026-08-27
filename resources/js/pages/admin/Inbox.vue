<script setup lang="ts">
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import { ref, nextTick, onUnmounted, watch } from 'vue';
import { index as adminInbox, show as adminInboxShow } from '@/routes/admin/inbox';
import { store as adminInboxStore } from '@/actions/App/Http/Controllers/Admin/InboxController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Send, User as UserIcon } from '@lucide/vue';

const props = defineProps<{
    conversations: Array<{
        id: number;
        user: { name: string; email: string };
        messages: Array<{ body: string; created_at: string }>;
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
const messagesList = ref(props.messages ? [...props.messages] : []);
const messagesContainer = ref<HTMLElement | null>(null);

const form = useForm({
    body: '',
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

let subscribedConversationId: number | null = null;

const setupEchoListener = (conversationId: number) => {
    const channelName = `conversation.${conversationId}`;

    echo().private(channelName).listen('.MessageSent', (message: any) => {
        if (!messagesList.value.some((item: any) => item.id === message.id)) {
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

watch(() => props.messages, (newMessages) => {
    if (newMessages) {
        messagesList.value = [...newMessages];
        scrollToBottom();
    }
}, { deep: true });

watch(() => props.activeConversation?.id, (newId) => {
    teardownEchoListener();

    if (newId) {
        setupEchoListener(newId);
    }
}, { immediate: true });

onUnmounted(teardownEchoListener);

const submit = () => {
    if (!form.body.trim() || !props.activeConversation) return;

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

    <div class="flex h-[calc(100vh-8rem)] overflow-hidden rounded-xl border border-sidebar-border/70 bg-sidebar shadow-sm dark:border-sidebar-border">

        <!-- Sidebar: Conversation List -->
        <div class="w-1/3 border-r border-sidebar-border/70 flex flex-col">
            <div class="p-4 border-b border-sidebar-border/70">
                <h2 class="font-semibold text-lg">Conversations</h2>
            </div>
            <div class="flex-1 overflow-y-auto">
                <div v-if="conversations.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                    No conversations found.
                </div>
                <Link
                    v-for="conv in conversations"
                    :key="conv.id"
                    :href="adminInboxShow(conv.id)"
                    :class="[
                        'flex flex-col gap-1 border-b border-sidebar-border/70 p-4 hover:bg-muted/50 transition-colors',
                        activeConversation?.id === conv.id ? 'bg-muted' : ''
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-sm">{{ conv.user.name }}</span>
                        <span v-if="conv.messages.length > 0" class="text-[10px] text-muted-foreground">
                            {{ new Date(conv.messages[0].created_at).toLocaleDateString() }}
                        </span>
                    </div>
                    <div class="text-xs text-muted-foreground truncate">
                        {{ conv.messages.length > 0 ? conv.messages[0].body : 'No messages' }}
                    </div>
                </Link>
            </div>
        </div>

        <!-- Main: Chat Area -->
        <div class="flex-1 flex flex-col bg-background">
            <template v-if="activeConversation">
                <!-- Chat Header -->
                <div class="flex items-center gap-3 border-b border-sidebar-border/70 p-4 bg-sidebar">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <UserIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="font-semibold">{{ activeConversation.user.name }}</h2>
                    </div>
                </div>

                <!-- Messages Area -->
                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div v-if="messagesList.length === 0" class="flex h-full items-center justify-center text-muted-foreground">
                        No messages yet.
                    </div>

                    <div
                        v-for="message in messagesList"
                        :key="message.id"
                        :class="[
                            'flex w-max max-w-[75%] flex-col gap-1 rounded-lg px-4 py-2 text-sm',
                            message.sender_id === currentUser.id
                                ? 'ml-auto bg-primary text-primary-foreground'
                                : 'bg-muted'
                        ]"
                    >
                        <!-- Nama pengirim sudah dihapus -->
                        <div>{{ message.body }}</div>
                        <div class="text-[10px] opacity-50 text-right mt-1">
                            {{ new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="border-t border-sidebar-border/70 p-4 bg-sidebar">
                    <form @submit.prevent="submit" class="flex gap-2">
                        <Input
                            v-model="form.body"
                            placeholder="Type your reply..."
                            class="flex-1"
                            :disabled="form.processing"
                            autocomplete="off"
                        />
                        <Button type="submit" :disabled="form.processing || !form.body.trim()">
                            <Send class="h-4 w-4" />
                            <span class="sr-only">Send</span>
                        </Button>
                    </form>
                </div>
            </template>
            <template v-else>
                <div class="flex h-full items-center justify-center text-muted-foreground">
                    Select a conversation to start messaging
                </div>
            </template>
        </div>

    </div>
</template>
