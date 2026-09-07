<script setup lang="ts">
import { Check, ChevronDown } from '@lucide/vue';
import {
    ComboboxRoot,
    ComboboxInput,
    ComboboxTrigger,
    ComboboxContent,
    ComboboxViewport,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxEmpty,
} from 'reka-ui';
import { computed, ref } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue: string;
    options: Array<{ id: string; name: string }>;
    placeholder?: string;
    disabled?: boolean;
    error?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const query = ref('');

const filteredOptions = computed(() =>
    query.value === ''
        ? props.options
        : props.options.filter((option) =>
              option.name.toLowerCase().includes(query.value.toLowerCase()),
          ),
);
</script>

<template>
    <ComboboxRoot
        :model-value="modelValue"
        @update:model-value="emit('update:modelValue', $event)"
        :disabled="disabled"
        class="relative"
        v-model:searchTerm="query"
    >
        <div
            :class="
                cn(
                    'flex h-10 w-full items-center justify-between rounded-md border bg-background px-3 py-2 text-sm ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2',
                    error
                        ? 'border-red-500 focus-within:ring-red-500'
                        : 'border-input',
                    disabled ? 'cursor-not-allowed opacity-50' : '',
                )
            "
        >
            <ComboboxInput
                :class="
                    cn(
                        'w-full bg-transparent outline-none placeholder:text-muted-foreground',
                        disabled ? 'cursor-not-allowed' : '',
                    )
                "
                :placeholder="placeholder"
                :display-value="
                    (val) => options.find((o) => o.id === val)?.name || ''
                "
            />
            <ComboboxTrigger
                :class="
                    cn(
                        'ml-2 shrink-0 opacity-50',
                        disabled ? 'cursor-not-allowed' : '',
                    )
                "
            >
                <ChevronDown class="h-4 w-4" />
            </ComboboxTrigger>
        </div>

        <ComboboxContent
            class="absolute z-50 mt-1 max-h-60 w-full overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95"
        >
            <ComboboxViewport class="p-1">
                <ComboboxEmpty class="py-6 text-center text-sm">
                    No option found.
                </ComboboxEmpty>

                <ComboboxItem
                    v-for="option in filteredOptions"
                    :key="option.id"
                    :value="option.id"
                    class="relative flex w-full cursor-default items-center rounded-sm py-1.5 pr-2 pl-8 text-sm outline-none select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                >
                    <span
                        class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center"
                    >
                        <ComboboxItemIndicator>
                            <Check class="h-4 w-4" />
                        </ComboboxItemIndicator>
                    </span>
                    {{ option.name }}
                </ComboboxItem>
            </ComboboxViewport>
        </ComboboxContent>
    </ComboboxRoot>
</template>
