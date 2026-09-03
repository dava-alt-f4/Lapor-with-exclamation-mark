<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}>();

const getLabel = (label: string, index: number, total: number) => {
    if (index === 0) {
        return 'Previous';
    }

    if (index === total - 1) {
        return 'Next';
    }

    return label;
};
</script>

<template>
    <div
        v-if="links && links.length > 3"
        class="flex items-center justify-center space-x-1"
    >
        <template v-for="(link, index) in links" :key="index">
            <!-- Disabled Link (No URL) -->
            <span
                v-if="link.url === null"
                class="cursor-not-allowed px-3 py-2 text-sm font-medium text-muted-foreground opacity-50"
                v-html="getLabel(link.label, index, links.length)"
            ></span>

            <!-- Active/Clickable Link -->
            <Link
                v-else
                :href="link.url"
                class="rounded-md px-3 py-2 text-sm font-medium transition-colors"
                :class="[
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'text-foreground hover:bg-muted',
                ]"
            >
                <span v-html="getLabel(link.label, index, links.length)"></span>
            </Link>
        </template>
    </div>
</template>
