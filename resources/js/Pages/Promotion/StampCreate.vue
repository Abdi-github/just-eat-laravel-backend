<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import DatePicker from 'primevue/datepicker';
import ToggleSwitch from 'primevue/toggleswitch';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
    restaurants: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    restaurant_id:      null as number | null,
    name:               '',
    description:        '',
    stamps_required:    10,
    reward_description: '',
    reward_type:        'FLAT',
    reward_value:       0,
    valid_from:         null as Date | null,
    valid_until:        null as Date | null,
    is_active:          true,
});

const REWARD_TYPES = [
    { label: 'Flat Amount (CHF)', value: 'FLAT' },
    { label: 'Percentage (%)', value: 'PERCENTAGE' },
];

function submit() {
    form.post(route('admin.stamp-cards.store'), {
        onSuccess: () => router.visit(route('admin.stamp-cards.index')),
    });
}
</script>

<template>
    <Head :title="t('stampCard.create')" />

    <div class="p-6 space-y-6">
        <div class="flex items-center gap-3">
            <Button icon="pi pi-arrow-left" severity="secondary" text
                @click="router.visit(route('admin.stamp-cards.index'))" />
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('stampCard.create') }}</h1>
        </div>

        <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-5 max-w-2xl">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ t('delivery.restaurant') }} *</label>
                    <Select v-model="form.restaurant_id"
                        :options="restaurants.map(r => ({ label: r.name, value: r.id }))"
                        option-label="label" option-value="value" class="w-full" />
                    <p v-if="form.errors.restaurant_id" class="text-red-500 text-xs mt-1">{{ form.errors.restaurant_id }}</p>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ t('common.name') }} *</label>
                    <InputText v-model="form.name" class="w-full" />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ t('common.description') }}</label>
                    <Textarea v-model="form.description" rows="2" class="w-full" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('stampCard.stampsRequired') }} *</label>
                    <InputNumber v-model="form.stamps_required" :min="1" :max="100" class="w-full" />
                    <p v-if="form.errors.stamps_required" class="text-red-500 text-xs mt-1">{{ form.errors.stamps_required }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('stampCard.rewardType') }} *</label>
                    <Select v-model="form.reward_type" :options="REWARD_TYPES"
                        option-label="label" option-value="value" class="w-full" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('stampCard.rewardValue') }} *</label>
                    <InputNumber v-model="form.reward_value" :min="0" :max-fraction-digits="2" class="w-full" />
                    <p v-if="form.errors.reward_value" class="text-red-500 text-xs mt-1">{{ form.errors.reward_value }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('stampCard.rewardDescription') }} *</label>
                    <InputText v-model="form.reward_description" class="w-full" />
                    <p v-if="form.errors.reward_description" class="text-red-500 text-xs mt-1">{{ form.errors.reward_description }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.startsAt') }}</label>
                    <DatePicker v-model="form.valid_from" show-time class="w-full" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('promotion.expiresAt') }}</label>
                    <DatePicker v-model="form.valid_until" show-time class="w-full" />
                </div>

                <div class="flex items-center gap-3">
                    <ToggleSwitch v-model="form.is_active" />
                    <label class="text-sm font-medium">{{ t('common.active') }}</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <Button :label="t('common.cancel')" severity="secondary" type="button"
                    @click="router.visit(route('admin.stamp-cards.index'))" />
                <Button :label="t('stampCard.create')" icon="pi pi-check" type="submit"
                    :loading="form.processing" />
            </div>
        </form>
    </div>
</template>
