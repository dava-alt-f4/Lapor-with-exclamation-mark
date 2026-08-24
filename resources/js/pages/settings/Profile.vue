<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Camera } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(null);
const { getInitials } = useInitials();

const avatarSource = computed(
    () => avatarPreview.value ?? user.value.avatar_url ?? null,
);

const openAvatarPicker = () => {
    avatarInput.value?.click();
};

const updateAvatarPreview = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }

    avatarPreview.value = URL.createObjectURL(file);
};

onBeforeUnmount(() => {
    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }
});
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile"
            description="Update your name and email address"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="flex items-center gap-4">
                <Avatar class="size-20 rounded-full">
                    <AvatarImage
                        v-if="avatarSource"
                        :src="avatarSource"
                        :alt="user.name"
                    />
                    <AvatarFallback class="text-lg font-semibold">
                        {{ getInitials(user.name) }}
                    </AvatarFallback>
                </Avatar>

                <div class="grid gap-2">
                    <Label for="avatar" class="text-sm font-medium"
                        >Profile picture</Label
                    >
                    <Button
                        type="button"
                        variant="outline"
                        class="w-fit"
                        @click="openAvatarPicker"
                    >
                        <Camera class="size-4" />
                        Change picture
                    </Button>
                </div>

                <input
                    id="avatar"
                    type="file"
                    name="avatar"
                    accept="image/jpeg,image/png,image/webp"
                    class="sr-only"
                    ref="avatarInput"
                    @change="updateAvatarPreview"
                />
            </div>
            <InputError class="mt-2" :message="errors.avatar" />

            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Email address"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Your email address is unverified.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Save</Button
                >
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>
