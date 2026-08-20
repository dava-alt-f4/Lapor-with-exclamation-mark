<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'Login with OTP',
        description: 'Insert an email to receive a code.',
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/otp-login');
};
</script>

<template>
    <Head title="Login dengan OTP" />

    <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :disabled="form.processing"
            >
                <Spinner v-if="form.processing" class="mr-2" />
                {{ form.processing ? 'Sending to your email' : 'Send OTP code' }}
            </Button>
        </div>
    </form>
</template>
