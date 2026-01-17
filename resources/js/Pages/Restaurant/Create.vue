<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { reactive, computed } from 'vue';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import ToggleSwitch from 'primevue/toggleswitch';
import Select from 'primevue/select';
import Button from 'primevue/button';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
    cantons: Array<{ id: number; code: string; name: Record<string, string> }>;
    cities:  Array<{ id: number; name: string; canton_id: number; zip_code: string }>;
    users:   Array<{ id: number; first_name: string; last_name: string; email: string }>;
}>();

const priceRangeOptions = [
    { label: 'Budget (CHF)', value: 'budget' },
    { label: 'Moderate (CHF CHF)', value: 'moderate' },
    { label: 'Upscale (CHF CHF CHF)', value: 'upscale' },
    { label: 'Fine Dining (CHF CHF CHF CHF)', value: 'fine_dining' },
];

const form = reactive({
    name:                    '',
    description:             '',
    phone:                   '',
    email:                   '',
    website:                 '',
    price_range:             'moderate' as string,
    minimum_order:           10,
    delivery_fee:            3.5,
    estimated_delivery_time: 30,
    accepts_pickup:          true,
    accepts_delivery:        true,
    user_id:                 null as number | null,
    canton_id:               null as number | null,
    city_id:                 null as number | null,
    street:                  '',
    zip_code:                '',
    processing:              false,
    errors:                  {} as Record<string, string>,
});

const cantonOptions = computed(() =>
    props.cantons.map(c => ({
        label: `${c.code} — ${c.name?.fr ?? c.name?.de ?? ''}`,
        value: c.id,
    }))
);

const filteredCities = computed(() => {
    if (!form.canton_id) return [];
    return props.cities
        .filter(c => c.canton_id === form.canton_id)
        .map(c => ({ label: `${c.name} (${c.zip_code})`, value: c.id }));
});

const userOptions = computed(() =>
    props.users.map(u => ({
        label: `${u.first_name} ${u.last_name} — ${u.email}`,
        value: u.id,
    }))
);

function onCantonChange() {
    form.city_id = null;
}

function submit() {
    form.processing = true;
    form.errors = {};

    router.post(route('admin.restaurants.store'), {
        name:                    form.name,
        description:             form.description || undefined,
        phone:                   form.phone || undefined,
        email:                   form.email || undefined,
        website:                 form.website || undefined,
        price_range:             form.price_range,
        minimum_order:           form.minimum_order,
        delivery_fee:            form.delivery_fee,
        estimated_delivery_time: form.estimated_delivery_time,
        accepts_pickup:          form.accepts_pickup,
        accepts_delivery:        form.accepts_delivery,
        user_id:                 form.user_id,
        canton_id:               form.canton_id,
        city_id:                 form.city_id,
        street:                  form.street,
        zip_code:                form.zip_code,
    }, {
        onError: (errors) => {
            form.errors = errors;
            form.processing = false;
        },
        onFinish: () => { form.processing = false; },
    });
}
</script>

<template>
    <Head :title="t('restaurant.create')" />

    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-3">
            <Link :href="route('admin.restaurants.index')">
                <Button icon="pi pi-arrow-left" text rounded />
            </Link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('restaurant.create') }}</h1>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <!-- Basic Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ t('restaurant.basicInfo') }}</h2>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('restaurant.name') }} *</label>
                    <InputText v-model="form.name" :invalid="!!form.errors.name" />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('common.description') }}</label>
                    <InputText v-model="form.description" />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('restaurant.priceRange') }} *</label>
                    <Select
                        v-model="form.price_range"
                        :options="priceRangeOptions"
                        option-label="label"
                        option-value="value"
                        :invalid="!!form.errors.price_range"
                    />
                </div>
            </div>

            <!-- Contact -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ t('common.contact') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('common.phone') }}</label>
                        <InputText v-model="form.phone" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('common.email') }}</label>
                        <InputText v-model="form.email" type="email" />
                        <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                    </div>
                    <div class="flex flex-col gap-1 col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('common.website') }}</label>
                        <InputText v-model="form.website" placeholder="https://" />
                        <small v-if="form.errors.website" class="text-red-500">{{ form.errors.website }}</small>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ t('common.address') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('location.canton') }} *</label>
                        <Select
                            v-model="form.canton_id"
                            :options="cantonOptions"
                            option-label="label"
                            option-value="value"
                            :invalid="!!form.errors.canton_id"
                            @change="onCantonChange"
                        />
                        <small v-if="form.errors.canton_id" class="text-red-500">{{ form.errors.canton_id }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('location.city') }} *</label>
                        <Select
                            v-model="form.city_id"
                            :options="filteredCities"
                            option-label="label"
                            option-value="value"
                            :disabled="!form.canton_id"
                            :invalid="!!form.errors.city_id"
                        />
                        <small v-if="form.errors.city_id" class="text-red-500">{{ form.errors.city_id }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('common.street') }} *</label>
                        <InputText v-model="form.street" :invalid="!!form.errors.street" />
                        <small v-if="form.errors.street" class="text-red-500">{{ form.errors.street }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('common.zipCode') }} *</label>
                        <InputText v-model="form.zip_code" :invalid="!!form.errors.zip_code" />
                        <small v-if="form.errors.zip_code" class="text-red-500">{{ form.errors.zip_code }}</small>
                    </div>
                </div>
            </div>

            <!-- Owner -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ t('restaurant.owner') }}</h2>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('common.user') }}</label>
                    <Select
                        v-model="form.user_id"
                        :options="userOptions"
                        option-label="label"
                        option-value="value"
                        filter
                        show-clear
                    />
                </div>
            </div>

            <!-- Delivery settings -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ t('restaurant.deliverySettings') }}</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('restaurant.minimumOrder') }} (CHF)</label>
                        <InputNumber v-model="form.minimum_order" :min="0" :min-fraction-digits="2" :max-fraction-digits="2" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('restaurant.deliveryFee') }} (CHF)</label>
                        <InputNumber v-model="form.delivery_fee" :min="0" :min-fraction-digits="2" :max-fraction-digits="2" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('restaurant.estimatedTime') }} (min)</label>
                        <InputNumber v-model="form.estimated_delivery_time" :min="0" />
                    </div>
                </div>
                <div class="flex gap-6">
                    <div class="flex items-center gap-2">
                        <ToggleSwitch v-model="form.accepts_delivery" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ t('restaurant.acceptsDelivery') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <ToggleSwitch v-model="form.accepts_pickup" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ t('restaurant.acceptsPickup') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <Button
                    type="submit"
                    :label="t('common.save')"
                    icon="pi pi-check"
                    :loading="form.processing"
                />
                <Link :href="route('admin.restaurants.index')">
                    <Button :label="t('common.cancel')" icon="pi pi-times" severity="secondary" type="button" />
                </Link>
            </div>
        </form>
    </div>
</template>
