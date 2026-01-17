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
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast   = useToast();
const confirm = useConfirm();

interface Promotion {
    id: number;
    code: string;
    title: string;
    description: string | null;
    type: 'percentage' | 'fixed';
    value: number;
    minimum_order: number | null;
    max_discount: number | null;
    usage_limit: number | null;
    usage_count: number;
    restaurant_id: number | null;
    is_active: boolean;
    starts_at: string | null;
    expires_at: string | null;
}

const props = defineProps<{
    promotion: Promotion;
    restaurants: Array<{ id: number; name: string }>;
}>();

const form = ref({
    code:          props.promotion.code,
    title:         props.promotion.title,
    description:   props.promotion.description ?? '',
    type:          props.promotion.type,
    value:         props.promotion.value,
    minimum_order: props.promotion.minimum_order,
    max_discount:  props.promotion.max_discount,
    usage_limit:   props.promotion.usage_limit,
    restaurant_id: props.promotion.restaurant_id,
    is_active:     props.promotion.is_active,
    starts_at:     props.promotion.starts_at ? new Date(props.promotion.starts_at) : null,
    expires_at:    props.promotion.expires_at ? new Date(props.promotion.expires_at) : null,
});

const errors     = ref<Record<string, string>>({});
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
        starts_at: form.value.starts_at ? (form.value.starts_at as Date).toISOString().slice(0, 10) : null,
        expires_at: form.value.expires_at ? (form.value.expires_at as Date).toISOString().slice(0, 10) : null,
    };

    router.put(route('admin.promotions.update', props.promotion.id), payload, {
        onSuccess: () => toast.add({ severity: 'success', summary: t('common.updated'), life: 2000 }),
        onError:   (e) => { errors.value = e; submitting.value = false; },
        onFinish:  () => { submitting.value = false; },
    });
}

function deletePromotion() {
    confirm.require({
        message: t('common.confirmDelete'),
        header:  t('common.delete'),
        icon:    'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(route('admin.promotions.destroy', props.promotion.id), {
                onSuccess: () => router.visit(route('admin.promotions.index')),
            });
        },
    });
}

const isExpired = props.promotion.expires_at && new Date(props.promotion.expires_at) < new Date();
</script>

<template>
    <Head :title="promotion.code" />

    <div class="p-6 max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <Button icon="pi pi-arrow-left" severity="secondary" text @click="router.visit(route('admin.promotions.index'))" />
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ promotion.code }}</h1>
                    <div class="flex gap-2 mt-1">
                        <Tag v-if="promotion.is_active && !isExpired" severity="success" :value="t('promotion.active')" rounded />
                        <Tag v-else-if="isExpired" severity="danger" :value="t('promotion.expired')" rounded />
                        <Tag v-else severity="secondary" :value="t('promotion.inactive')" rounded />
                    </div>
                </div>
            </div>
            <Button icon="pi pi-trash" severity="danger" :label="t('common.delete')" @click="deletePromotion" />
        </div>

        <!-- Stats bar -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm text-center">
                <p class="text-2xl font-bold text-primary-600">{{ promotion.usage_count }}</p>
                <p class="text-sm text-gray-500">{{ t('promotion.usageCount') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm text-center">
                <p class="text-2xl font-bold">{{ promotion.usage_limit ?? '∞' }}</p>
                <p class="text-sm text-gray-500">{{ t('promotion.usageLimit') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm text-center">
                <p class="text-2xl font-bold">
                    {{ promotion.type === 'percentage' ? promotion.value + '%' : 'CHF ' + promotion.value }}
                </p>
                <p class="text-sm text-gray-500">{{ t('promotion.value') }}</p>
            </div>
        </div>

        <!-- Edit Form -->
        <form class="space-y-5 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm" @submit.prevent="submit">
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

            <div>
                <label class="block text-sm font-medium mb-1">{{ t('common.description') }}</label>
                <InputText v-model="form.description" class="w-full" />
            </div>

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

            <div class="flex items-center gap-2">
                <Checkbox v-model="form.is_active" :binary="true" inputId="is_active" />
                <label for="is_active" class="text-sm font-medium">{{ t('promotion.active') }}</label>
            </div>

            <div class="flex gap-3 pt-2">
                <Button type="submit" :label="t('common.save')" icon="pi pi-check" :loading="submitting" />
                <Button :label="t('common.cancel')" severity="secondary" @click="router.visit(route('admin.promotions.index'))" />
            </div>
        </form>
    </div>
</template>
