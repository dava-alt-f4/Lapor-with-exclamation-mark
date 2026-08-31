<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue';
import { ref, watch, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard, login } from '@/routes';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
    },
});

const currentStep = ref(1);

const form = useForm('post', '/register', {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    country: 'Indonesia',
    province: '',
    city: '',
    district: '',
});
form.setValidationTimeout(0);

// Api
const provinces = ref<Array<{ id: string; name: string }>>([]);
const cities = ref<Array<{ id: string; name: string }>>([]);
const districts = ref<Array<{ id: string; name: string }>>([]);

const selectedProvinceId = ref('');
const selectedCityId = ref('');
const selectedDistrictId = ref('');

// Frontend Validation Errors
const step1Errors = ref({
    name: '',
    password: '',
    password_confirmation: '',
});

const touched = ref({
    name: false,
    password: false,
    password_confirmation: false,
});

const debounceTimers: Record<string, ReturnType<typeof setTimeout>> = {};

type Step1Field = keyof typeof step1Errors.value;

const validateField = (field: Step1Field) => {
    if (field === 'name') {
        step1Errors.value.name = !form.name.trim()
            ? 'Full Name is required.'
            : '';
    } else if (field === 'password') {
        if (!form.password) {
            step1Errors.value.password = 'Password is required.';
        } else if (form.password.length < 8) {
            step1Errors.value.password =
                'Password must be at least 8 characters.';
        } else {
            step1Errors.value.password = '';
        }

        // Re-validate confirmation if already touched
        if (touched.value.password_confirmation) {
            validateField('password_confirmation');
        }
    } else if (field === 'password_confirmation') {
        if (!form.password_confirmation) {
            step1Errors.value.password_confirmation =
                'Please confirm your password.';
        } else if (form.password !== form.password_confirmation) {
            step1Errors.value.password_confirmation = 'Passwords do not match.';
        } else {
            step1Errors.value.password_confirmation = '';
        }
    }
};

const handleBlur = (field: Step1Field) => {
    touched.value[field] = true;
    validateField(field);
};

const handleInput = (field: Step1Field) => {
    touched.value[field] = true;
    clearTimeout(debounceTimers[field]);
    debounceTimers[field] = setTimeout(() => validateField(field), 1000);
};

const emailValidationPending = ref(false);
const emailDebounceTimer = ref<ReturnType<typeof setTimeout> | null>(null);

const validateEmail = (onValid?: () => void, force = false) => {
    emailValidationPending.value = true;

    const options = {
        onPrecognitionSuccess: () => {
            emailValidationPending.value = false;
            form.forgetError('email');
            onValid?.();
        },
        onValidationError: () => {
            emailValidationPending.value = false;
        },
        onFinish: () => {
            emailValidationPending.value = false;
        },
    };

    if (force) {
        form.validate({
            only: ['email'],
            ...options,
        });
    } else {
        form.validate('email', options);
    }
};

const handleEmailInput = () => {
    if (emailDebounceTimer.value) {
        clearTimeout(emailDebounceTimer.value);
    }

    emailDebounceTimer.value = setTimeout(() => {
        validateEmail();
    }, 500);
};

const handleEmailBlur = () => {
    if (emailDebounceTimer.value) {
        clearTimeout(emailDebounceTimer.value);
    }

    validateEmail();
};

const step2Errors = ref({
    province: '',
    city: '',
    district: '',
});

onMounted(async () => {
    try {
        const response = await fetch(
            'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json',
        );
        provinces.value = await response.json();
    } catch (error) {
        console.error('Failed to fetch provinces:', error);
    }
});

watch(selectedProvinceId, async (newId) => {
    cities.value = [];
    districts.value = [];
    selectedCityId.value = '';
    selectedDistrictId.value = '';
    form.city = '';
    form.district = '';
    step2Errors.value.province = '';

    if (newId) {
        const prov = provinces.value.find((p) => p.id === newId);

        if (prov) {
            form.province = prov.name;
        }

        try {
            const response = await fetch(
                `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${newId}.json`,
            );
            cities.value = await response.json();
        } catch (error) {
            console.error('Failed to fetch cities:', error);
        }
    } else {
        form.province = '';
    }
});

watch(selectedCityId, async (newId) => {
    districts.value = [];
    selectedDistrictId.value = '';
    form.district = '';
    step2Errors.value.city = '';

    if (newId) {
        const city = cities.value.find((c) => c.id === newId);

        if (city) {
            form.city = city.name;
        }

        try {
            const response = await fetch(
                `https://www.emsifa.com/api-wilayah-indonesia/api/districts/${newId}.json`,
            );
            districts.value = await response.json();
        } catch (error) {
            console.error('Failed to fetch districts:', error);
        }
    } else {
        form.city = '';
    }
});

watch(selectedDistrictId, (newId) => {
    step2Errors.value.district = '';

    if (newId) {
        const dist = districts.value.find((d) => d.id === newId);

        if (dist) {
            form.district = dist.name;
        }
    } else {
        form.district = '';
    }
});

// Navigation & Validation
const nextStep = () => {
    let isValid = true;
    touched.value = {
        name: true,
        password: true,
        password_confirmation: true,
    };
    step1Errors.value = {
        name: '',
        password: '',
        password_confirmation: '',
    };

    if (!form.name.trim()) {
        step1Errors.value.name = 'Full Name is required.';
        isValid = false;
    }

    if (!form.password) {
        step1Errors.value.password = 'Password is required.';
        isValid = false;
    } else if (form.password.length < 8) {
        step1Errors.value.password = 'Password must be at least 8 characters.';
        isValid = false;
    }

    if (!form.password_confirmation) {
        step1Errors.value.password_confirmation =
            'Please confirm your password.';
        isValid = false;
    } else if (form.password !== form.password_confirmation) {
        step1Errors.value.password_confirmation = 'Passwords do not match.';
        isValid = false;
    }

    if (!isValid) {
        return;
    }

    validateEmail(() => {
        // console.log('moving to step 2');
        currentStep.value = 2;
    }, true);
};

const prevStep = () => {
    currentStep.value--;
};

const validateStep2AndProceed = () => {
    let isValid = true;
    step2Errors.value = { province: '', city: '', district: '' };

    if (!selectedProvinceId.value) {
        step2Errors.value.province = 'Please select a province.';
        isValid = false;
    }

    if (!selectedCityId.value) {
        step2Errors.value.city = 'Please select a city or regency.';
        isValid = false;
    }

    if (!selectedDistrictId.value) {
        step2Errors.value.district = 'Please select a district.';
        isValid = false;
    }

    if (isValid) {
        currentStep.value = 3;
    }
};

const submit = () => {
    form.submit({
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
            router.visit(dashboard.url());
        },
    });
};
</script>

<template>
    <Head title="Register" />

    <!-- Progress Indicator -->
    <div class="mb-8">
        <div class="relative flex w-full items-start justify-between">
            <!-- Connecting Line Background -->
            <div
                class="absolute top-4 left-0 z-0 h-1.5 w-full -translate-y-1/2 rounded-full bg-muted"
            ></div>
            <!-- Active Connecting Line -->
            <div
                class="absolute top-4 left-0 z-0 h-1.5 -translate-y-1/2 rounded-full bg-primary transition-all duration-500 ease-in-out"
                :style="{
                    width:
                        currentStep === 1
                            ? '0%'
                            : currentStep === 2
                              ? '50%'
                              : '100%',
                }"
            ></div>

            <!-- Step 1 -->
            <div
                class="relative z-10 flex flex-col items-center gap-2 bg-background px-2"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold ring-4 ring-background transition-colors duration-300"
                    :class="
                        currentStep >= 1
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-muted-foreground'
                    "
                >
                    1
                </div>
                <span
                    class="text-xs font-medium"
                    :class="
                        currentStep >= 1
                            ? 'text-foreground'
                            : 'text-muted-foreground'
                    "
                    >Account</span
                >
            </div>

            <!-- Step 2 -->
            <div
                class="relative z-10 flex flex-col items-center gap-2 bg-background px-2"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold ring-4 ring-background transition-colors duration-300"
                    :class="
                        currentStep >= 2
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-muted-foreground'
                    "
                >
                    2
                </div>
                <span
                    class="text-xs font-medium"
                    :class="
                        currentStep >= 2
                            ? 'text-foreground'
                            : 'text-muted-foreground'
                    "
                    >Address</span
                >
            </div>

            <!-- Step 3 -->
            <div
                class="relative z-10 flex flex-col items-center gap-2 bg-background px-2"
            >
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold ring-4 ring-background transition-colors duration-300"
                    :class="
                        currentStep >= 3
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-muted-foreground'
                    "
                >
                    3
                </div>
                <span
                    class="text-xs font-medium"
                    :class="
                        currentStep >= 3
                            ? 'text-foreground'
                            : 'text-muted-foreground'
                    "
                    >Finish</span
                >
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div>
        <!-- STEP 1 -->
        <form
            v-show="currentStep === 1"
            novalidate
            @submit.prevent="nextStep"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Full Name</Label>
                    <Input
                        id="name"
                        type="text"
                        v-model="form.name"
                        autofocus
                        autocomplete="name"
                        placeholder="John Doe"
                        :class="
                            step1Errors.name
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : ''
                        "
                        @blur="handleBlur('name')"
                        @input="handleInput('name')"
                    />
                    <span
                        v-if="step1Errors.name"
                        class="text-sm font-medium text-red-500"
                        >{{ step1Errors.name }}</span
                    >
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        autocomplete="email"
                        placeholder="email@example.com"
                        :class="
                            form.invalid('email')
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : ''
                        "
                        @input="handleEmailInput"
                        @blur="handleEmailBlur"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        autocomplete="new-password"
                        placeholder="Password"
                        :passwordrules="passwordRules"
                        :class="
                            step1Errors.password
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : ''
                        "
                        @blur="handleBlur('password')"
                        @input="handleInput('password')"
                    />
                    <span
                        v-if="step1Errors.password"
                        class="text-sm font-medium text-red-500"
                        >{{ step1Errors.password }}</span
                    >
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        :passwordrules="passwordRules"
                        :class="
                            step1Errors.password_confirmation
                                ? 'border-red-500 focus-visible:ring-red-500'
                                : ''
                        "
                        @blur="handleBlur('password_confirmation')"
                        @input="handleInput('password_confirmation')"
                    />
                    <span
                        v-if="step1Errors.password_confirmation"
                        class="text-sm font-medium text-red-500"
                        >{{ step1Errors.password_confirmation }}</span
                    >
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full"> Next Step </Button>
            </div>
        </form>

        <!-- STEP 2 -->
        <form
            v-show="currentStep === 2"
            @submit.prevent="validateStep2AndProceed"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="country">Country</Label>
                    <Input
                        id="country"
                        type="text"
                        v-model="form.country"
                        disabled
                        class="cursor-not-allowed bg-muted text-muted-foreground"
                    />
                    <InputError :message="form.errors.country" />
                </div>

                <div class="grid gap-2">
                    <Label for="province">Province</Label>
                    <select
                        id="province"
                        v-model="selectedProvinceId"
                        :class="[
                            'flex h-10 w-full items-center justify-between rounded-md border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                            step2Errors.province
                                ? 'border-red-500 focus:ring-red-500'
                                : 'border-input',
                        ]"
                    >
                        <option value="" disabled>Select Province</option>
                        <option
                            v-for="prov in provinces"
                            :key="prov.id"
                            :value="prov.id"
                        >
                            {{ prov.name }}
                        </option>
                    </select>
                    <span
                        v-if="step2Errors.province"
                        class="text-sm font-medium text-red-500"
                        >{{ step2Errors.province }}</span
                    >
                    <InputError :message="form.errors.province" />
                </div>

                <div class="grid gap-2">
                    <Label for="city">City / Regency</Label>
                    <select
                        id="city"
                        v-model="selectedCityId"
                        :disabled="!selectedProvinceId"
                        :class="[
                            'flex h-10 w-full items-center justify-between rounded-md border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                            step2Errors.city
                                ? 'border-red-500 focus:ring-red-500'
                                : 'border-input',
                        ]"
                    >
                        <option value="" disabled>Select City/Regency</option>
                        <option
                            v-for="city in cities"
                            :key="city.id"
                            :value="city.id"
                        >
                            {{ city.name }}
                        </option>
                    </select>
                    <span
                        v-if="step2Errors.city"
                        class="text-sm font-medium text-red-500"
                        >{{ step2Errors.city }}</span
                    >
                    <InputError :message="form.errors.city" />
                </div>

                <div class="grid gap-2">
                    <Label for="district">District</Label>
                    <select
                        id="district"
                        v-model="selectedDistrictId"
                        :disabled="!selectedCityId"
                        :class="[
                            'flex h-10 w-full items-center justify-between rounded-md border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                            step2Errors.district
                                ? 'border-red-500 focus:ring-red-500'
                                : 'border-input',
                        ]"
                    >
                        <option value="" disabled>Select District</option>
                        <option
                            v-for="dist in districts"
                            :key="dist.id"
                            :value="dist.id"
                        >
                            {{ dist.name }}
                        </option>
                    </select>
                    <span
                        v-if="step2Errors.district"
                        class="text-sm font-medium text-red-500"
                        >{{ step2Errors.district }}</span
                    >
                    <InputError :message="form.errors.district" />
                </div>

                <div class="mt-2 flex gap-4">
                    <Button
                        type="button"
                        variant="outline"
                        class="w-1/2"
                        @click="prevStep"
                    >
                        Back
                    </Button>
                    <Button type="submit" class="w-1/2"> Evaluate </Button>
                </div>
            </div>
        </form>

        <!-- STEP 3 -->
        <form
            v-show="currentStep === 3"
            @submit.prevent="submit"
            class="flex flex-col gap-6"
        >
            <div
                class="overflow-hidden rounded-xl border border-border bg-card text-card-foreground shadow-sm"
            >
                <div class="space-y-6 p-6">
                    <!-- Account Details Summary -->
                    <div class="space-y-3">
                        <h3
                            class="text-lg leading-none font-semibold tracking-tight"
                        >
                            Account Details
                        </h3>
                        <div class="grid gap-3">
                            <div class="grid gap-1">
                                <Label class="text-xs text-muted-foreground"
                                    >Full Name</Label
                                >
                                <Input
                                    :value="form.name"
                                    readonly
                                    class="h-9 cursor-not-allowed bg-muted/50 text-muted-foreground"
                                />
                            </div>
                            <div class="grid gap-1">
                                <Label class="text-xs text-muted-foreground"
                                    >Email Address</Label
                                >
                                <Input
                                    :value="form.email"
                                    readonly
                                    class="h-9 cursor-not-allowed bg-muted/50 text-muted-foreground"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="h-px w-full bg-border"></div>

                    <!-- Address Detail -->
                    <div class="space-y-3">
                        <h3
                            class="text-lg leading-none font-semibold tracking-tight"
                        >
                            Address Details
                        </h3>
                        <div class="grid gap-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="grid gap-1">
                                    <Label class="text-xs text-muted-foreground"
                                        >Country</Label
                                    >
                                    <Input
                                        v-model="form.country"
                                        readonly
                                        class="h-9 cursor-not-allowed bg-muted/50 text-muted-foreground"
                                    />
                                </div>
                                <div class="grid gap-1">
                                    <Label class="text-xs text-muted-foreground"
                                        >Province</Label
                                    >
                                    <Input
                                        :value="form.province"
                                        readonly
                                        class="h-9 cursor-not-allowed bg-muted/50 text-muted-foreground"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="grid gap-1">
                                    <Label class="text-xs text-muted-foreground"
                                        >City / Regency</Label
                                    >
                                    <Input
                                        :value="form.city"
                                        readonly
                                        class="h-9 cursor-not-allowed bg-muted/50 text-muted-foreground"
                                    />
                                </div>
                                <div class="grid gap-1">
                                    <Label class="text-xs text-muted-foreground"
                                        >District</Label
                                    >
                                    <Input
                                        :value="form.district"
                                        readonly
                                        class="h-9 cursor-not-allowed bg-muted/50 text-muted-foreground"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2 flex gap-4">
                <Button
                    type="button"
                    variant="outline"
                    class="w-1/2"
                    @click="prevStep"
                    :disabled="form.processing"
                >
                    Back
                </Button>
                <Button type="submit" class="w-1/2" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2" />
                    Confirm & Register
                </Button>
            </div>
        </form>
        <div class="mt-3 text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink :href="login()" :tabindex="5">Sign In</TextLink>
        </div>
    </div>
</template>
