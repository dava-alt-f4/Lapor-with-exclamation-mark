<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'OTP Verification',
        description:
            'Enter the 6-digit OTP code that has been sent to your email.',
    },
});

defineProps<{
    email: string;
}>();

const form = useForm({
    otp: '',
});

const submit = () => {
    form.post('/otp-verify');
};
</script>

<template>
    <Head title="Verifikasi OTP" />

    <div class="mb-6 text-sm text-muted-foreground">
        OTP code has been delivered to <strong class="text-primary">{{ email }}</strong
        >.
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="otp">OTP code</Label>
                <Input
                    id="otp"
                    type="text"
                    v-model="form.otp"
                    required
                    autofocus
                    maxlength="6"
                    autocomplete="one-time-code"
                    class="text-center text-lg tracking-widest"
                    placeholder="••••••"
                />
                <InputError :message="form.errors.otp" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :disabled="form.processing"
            >
                <Spinner v-if="form.processing" class="mr-2" />
                {{ form.processing ? 'Logging in' : 'Verify and Login' }}
            </Button>
        </div>
    </form>
</template>
