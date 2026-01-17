<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();

const props = defineProps<{
    restaurants: Array<{ id: number; name: string }>;
}>();

const form = ref({
    code: '',
    title: '',
    description: '',
    type: 'percentage' as 'percentage' | 'fixed',
    value: 0,
    minimum_order: null as number | null,
    max_discount: null as number | null,
    usage_limit: null as number | null,
    restaurant_id: null as number | null,
    is_active: true,
    starts_at: null as Date | null,
    expires_at: null as Date | null,
});

const errors = ref<Record<string, string>>({});
const submitting = ref(false);

const typeOptions = [
    { label: t('promotion.percentage'), value: 'percentage' },
    { label: t('promotion.fixed'),      value: 'fixed' },
];

const restaurantOptions = [
    { label: t('promotion.global'), value: null },
    ...props.restaurants.map(r => ({ label: r.name, value: r.id })),
];

function submit() {
    submitting.value = true;
    errors.value = {};

    const payload = {
        ...form.value,
        starts_at: form.value.starts_at ? form.value.starts_at.toISOString().slice(0, 10) : null,
        expires_at: form.value.expires_at ? form.value.expires_at.toISOString().slice(0, 10) : null,
    };

    router.post(route('admin.promotions.store'), payload, {
        onSuccess: () => toast.add({ severity: 'success', summary: t('common.created'), life: 2000 }),
        onError:   (e) => { errors.value = e; submitting.value = false; },
        onFinish:  () => { submitting.value = false; },
    });
}
</script>

<template>
    <Head :title="t('promotion.create')" />

    <div class="p-6 max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <Button icon="pi pi-arrow-left" severity="secondary" text @click="router.visit(route('admin.promotions.index'))" />
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('promotion.create') }}</h1>
        </div>

        <form class="space-y-5 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm" @submit.prevent="submit">
            <!-- Code & Title -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.code') }} *</label>
                    <InputText v-model="form.code" class="w-full" :class="{ 'p-invalid': errors.code }" />
                    <small v-if="errors.code" class="text-red-500">{{ errors.code }}</small>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('common.title') }} *</label>
                    <InputText v-model="form.title" class="w-full" :class="{ 'p-invalid': errors.title }" />
                    <small v-if="errors.title" class="text-red-500">{{ errors.title }}</small>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium mb-1">{{ t('common.description') }}</label>
                <InputText v-model="form.description" class="w-full" />
            </div>

            <!-- Type & Value -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.type') }} *</label>
                    <Select v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.value') }} *</label>
                    <InputNumber v-model="form.value" :min="0" :max="form.type === 'percentage' ? 100 : 9999" :suffix="form.type === 'percentage' ? '%' : ''" class="w-full" />
                    <small v-if="errors.value" class="text-red-500">{{ errors.value }}</small>
                </div>
            </div>

            <!-- Min Order & Max Discount -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.minOrder') }}</label>
                    <InputNumber v-model="form.minimum_order" :min="0" prefix="CHF " class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.maxDiscount') }}</label>
                    <InputNumber v-model="form.max_discount" :min="0" prefix="CHF " class="w-full" />
                </div>
            </div>

            <!-- Usage Limit & Restaurant -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.usageLimit') }}</label>
                    <InputNumber v-model="form.usage_limit" :min="0" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('restaurant.title') }}</label>
                    <Select v-model="form.restaurant_id" :options="restaurantOptions" optionLabel="label" optionValue="value" class="w-full" />
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.startsAt') }}</label>
                    <DatePicker v-model="form.starts_at" dateFormat="yy-mm-dd" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.expiresAt') }}</label>
                    <DatePicker v-model="form.expires_at" dateFormat="yy-mm-dd" class="w-full" />
                </div>
            </div>

            <!-- Active -->
            <div class="flex items-center gap-2">
                <Checkbox v-model="form.is_active" :binary="true" inputId="is_active" />
                <label for="is_active" class="text-sm font-medium">{{ t('promotion.active') }}</label>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <Button type="submit" :label="t('common.save')" icon="pi pi-check" :loading="submitting" />
                <Button :label="t('common.cancel')" severity="secondary" @click="router.visit(route('admin.promotions.index'))" />
            </div>
        </form>
    </div>
</template>
