<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    status?: number;
}>();

const title = computed(() => {
    const statusCode = props.status ?? 500;
    const titles: Record<number, string> = {
        503: '503 | Service Unavailable',
        500: '500 | Server Error',
        404: '404 | Page Not Found',
        403: '403 | Forbidden',
        419: '419 | Page Expired',
    };

    return titles[statusCode] || 'Error';
});

const description = computed(() => {
    const statusCode = props.status ?? 500;
    const descriptions: Record<number, string> = {
        503: 'Sorry, our server is currently undergoing maintenance.',
        500: 'Oops, an error occurred on our server.',
        404: 'The page you are looking for was not found or has been moved.',
        403: 'You do not have permission to access this page.',
        419: 'Your session has ended. Please reload the page.',
    };

    return descriptions[statusCode] || 'Unexpected error occurred.';
});
</script>

<template>
    <div
        class="flex min-h-screen scrollbar-none items-center justify-center bg-background text-gray-100"
    >
        <div class="text-center">
            <h1 class="mb-4 text-6xl font-bold">{{ title }}</h1>
            <p class="mb-6 text-xl">{{ description }}</p>
            <Button
                size="lg"
                class="bg-blue-500 text-primary transition hover:bg-blue-400"
                as-child
            >
                <Link href="/dashboard">
                    <ArrowLeft />
                    <span>Return to Dashboard</span>
                </Link>
            </Button>
        </div>
    </div>
</template>
