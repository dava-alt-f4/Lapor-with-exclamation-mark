<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Send } from '@lucide/vue';
import { ref, onMounted, nextTick, watch } from 'vue';
import { store as chatStore } from '@/actions/App/Http/Controllers/ChatController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes';

const props = defineProps<{
    conversation: { id: number };
    messages: Array<{
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
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const currentUser = page.props.auth.user;
const messagesList = ref([...props.messages]);
const messagesContainer = ref<HTMLElement | null>(null);
const inputMessage = ref<InstanceType<typeof Input> | null>(null);

const form = useForm({
    body: '',
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop =
                messagesContainer.value.scrollHeight;
        }
    });
};

watch(
    () => props.messages,
    (newMessages) => {
        messagesList.value = [...newMessages];
        scrollToBottom();
    },
    { deep: true },
);

onMounted(() => {
    scrollToBottom();
    inputMessage.value?.$el?.focus();
});

useEcho(`conversation.${props.conversation.id}`, '.MessageSent', (e: any) => {
    messagesList.value.push(e);
    scrollToBottom();
});

const submit = () => {
    if (!form.body.trim()) {
        return;
    }

    form.post(chatStore.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        },
        onFinish: () => {
            nextTick(() => {
                inputMessage.value?.$el?.focus();
            });
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-[calc(100vh-8rem)] flex-col rounded-xl border border-sidebar-border/70 bg-sidebar shadow-sm dark:border-sidebar-border"
    >
        <!-- Chat Header -->
        <div class="flex items-center border-b border-sidebar-border/70 p-4">
            <h2 class="text-lg font-semibold">Chat with Admin</h2>
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
                No messages yet. Start the conversation!
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
                <!-- Nama pengirim sudah dihapus -->
                <div>{{ message.body }}</div>
                <div class="mt-1 text-right text-[10px] opacity-50">
                    {{
                        new Date(message.created_at).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit',
                        })
                    }}
                </div>
            </div>
        </div>

        <!-- Message Input -->
        <div class="border-t border-sidebar-border/70 p-4">
            <form @submit.prevent="submit" class="flex gap-2">
                <Input
                    v-model="form.body"
                    placeholder="Type your message..."
                    class="flex-1"
                    :disabled="form.processing"
                    autocomplete="off"
                    ref="inputMessage"
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
    </div>
</template>
