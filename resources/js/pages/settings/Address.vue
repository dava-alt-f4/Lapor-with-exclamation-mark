<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import AddressController from '@/actions/App/Http/Controllers/Settings/AddressController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/address';

type Address = {
    country: string;
    province: string | null;
    city: string | null;
    district: string | null;
};

type Region = {
    id: string;
    name: string;
};

const props = defineProps<{
    address: Address;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Address settings',
                href: edit(),
            },
        ],
    },
});

const provinces = ref<Region[]>([]);
const cities = ref<Region[]>([]);
const districts = ref<Region[]>([]);
const selectedProvinceId = ref('');
const selectedCityId = ref('');
const selectedDistrictId = ref('');
const loading = ref(true);
const apiError = ref('');
const initializing = ref(true);

const addressValues = ref<Address>({
    country: props.address.country || 'Indonesia',
    province: props.address.province,
    city: props.address.city,
    district: props.address.district,
});

const fetchRegions = async (url: string): Promise<Region[]> => {
    const response = await fetch(url);

    if (!response.ok) {
        throw new Error('Unable to load address data.');
    }

    return response.json() as Promise<Region[]>;
};

const setApiError = (error: unknown) => {
    apiError.value =
        error instanceof Error
            ? error.message
            : 'Unable to load address data. Please try again later.';
};

const loadCities = async (provinceId: string) => {
    cities.value = await fetchRegions(
        `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`,
    );
};

const loadDistricts = async (cityId: string) => {
    districts.value = await fetchRegions(
        `https://www.emsifa.com/api-wilayah-indonesia/api/districts/${cityId}.json`,
    );
};

onMounted(async () => {
    try {
        provinces.value = await fetchRegions(
            'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json',
        );

        const province = provinces.value.find(
            (item) => item.name === props.address.province,
        );

        if (province) {
            selectedProvinceId.value = province.id;
            await loadCities(province.id);

            const city = cities.value.find(
                (item) => item.name === props.address.city,
            );

            if (city) {
                selectedCityId.value = city.id;
                await loadDistricts(city.id);

                const district = districts.value.find(
                    (item) => item.name === props.address.district,
                );

                if (district) {
                    selectedDistrictId.value = district.id;
                }
            }
        }
    } catch (error) {
        setApiError(error);
    } finally {
        initializing.value = false;
        loading.value = false;
    }
});

watch(selectedProvinceId, async (provinceId) => {
    if (initializing.value) {
        return;
    }

    cities.value = [];
    districts.value = [];
    selectedCityId.value = '';
    selectedDistrictId.value = '';
    addressValues.value.province = '';
    addressValues.value.city = '';
    addressValues.value.district = '';

    if (!provinceId) {
        return;
    }

    const province = provinces.value.find((item) => item.id === provinceId);

    if (province) {
        addressValues.value.province = province.name;
    }

    loading.value = true;

    try {
        await loadCities(provinceId);
    } catch (error) {
        setApiError(error);
    } finally {
        loading.value = false;
    }
});

watch(selectedCityId, async (cityId) => {
    if (initializing.value) {
        return;
    }

    districts.value = [];
    selectedDistrictId.value = '';
    addressValues.value.city = '';
    addressValues.value.district = '';

    if (!cityId) {
        return;
    }

    const city = cities.value.find((item) => item.id === cityId);

    if (city) {
        addressValues.value.city = city.name;
    }

    loading.value = true;

    try {
        await loadDistricts(cityId);
    } catch (error) {
        setApiError(error);
    } finally {
        loading.value = false;
    }
});

watch(selectedDistrictId, (districtId) => {
    if (initializing.value) {
        return;
    }

    const district = districts.value.find((item) => item.id === districtId);

    addressValues.value.district = district?.name || '';
});

</script>

<template>
    <Head title="Address settings" />

    <h1 class="sr-only">Address settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Address"
            description="Update your address."
        />

        <div
            v-if="apiError"
            class="rounded-md border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
            role="alert"
        >
            {{ apiError }}
        </div>

        <Form
            v-bind="AddressController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <input type="hidden" name="country" :value="addressValues.country" />
            <input type="hidden" name="province" :value="addressValues.province || ''" />
            <input type="hidden" name="city" :value="addressValues.city || ''" />
            <input type="hidden" name="district" :value="addressValues.district || ''" />

            <div class="grid gap-2">
                <Label for="country">Country</Label>
                <div
                    id="country"
                    class="flex h-10 w-full items-center rounded-md border border-input bg-muted px-3 py-2 text-sm text-muted-foreground"
                >
                    {{ addressValues.country }}
                </div>
                <InputError :message="errors.country" />
            </div>

            <div class="grid gap-2">
                <Label for="province">Province</Label>
                <select
                    id="province"
                    v-model="selectedProvinceId"
                    :disabled="loading || !!apiError"
                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">Select province</option>
                    <option v-for="province in provinces" :key="province.id" :value="province.id">
                        {{ province.name }}
                    </option>
                </select>
                <InputError :message="errors.province" />
            </div>

            <div class="grid gap-2">
                <Label for="city">City / Regency</Label>
                <select
                    id="city"
                    v-model="selectedCityId"
                    :disabled="!selectedProvinceId || loading || !!apiError"
                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">Select city or regency</option>
                    <option v-for="city in cities" :key="city.id" :value="city.id">
                        {{ city.name }}
                    </option>
                </select>
                <InputError :message="errors.city" />
            </div>

            <div class="grid gap-2">
                <Label for="district">District</Label>
                <select
                    id="district"
                    v-model="selectedDistrictId"
                    :disabled="!selectedCityId || loading || !!apiError"
                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">Select district</option>
                    <option v-for="district in districts" :key="district.id" :value="district.id">
                        {{ district.name }}
                    </option>
                </select>
                <InputError :message="errors.district" />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    type="submit"
                    :disabled="processing || loading || !!apiError"
                    data-test="update-address-button"
                >
                    {{ processing ? 'Saving...' : 'Save' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
