<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import DatePicker from 'primevue/datepicker';
import ToggleSwitch from 'primevue/toggleswitch';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast   = useToast();
const confirm = useConfirm();

const props = defineProps<{
    stampCard: {
        id: number;
        name: string;
        description?: string;
        stamps_required: number;
        reward_description: string;
        reward_type: string;
        reward_value: number;
        valid_from?: string;
        valid_until?: string;
        is_active: boolean;
        created_at: string;
        restaurant?: { id: number; name: string };
    };
    restaurants: Array<{ id: number; name: string }>;
}>();

const REWARD_TYPES = [
    { label: 'Flat Amount (CHF)', value: 'FLAT' },
    { label: 'Percentage (%)', value: 'PERCENTAGE' },
];

const form = useForm({
    restaurant_id:      props.stampCard.restaurant?.id ?? null,
    name:               props.stampCard.name,
    description:        props.stampCard.description ?? '',
    stamps_required:    props.stampCard.stamps_required,
    reward_description: props.stampCard.reward_description,
    reward_type:        props.stampCard.reward_type,
    reward_value:       props.stampCard.reward_value,
    valid_from:         props.stampCard.valid_from ? new Date(props.stampCard.valid_from) : null,
    valid_until:        props.stampCard.valid_until ? new Date(props.stampCard.valid_until) : null,
    is_active:          props.stampCard.is_active,
});

function submit() {
    form.put(route('admin.stamp-cards.update', props.stampCard.id), {
        onSuccess: () => toast.add({ severity: 'success', summary: t('common.updated'), life: 2000 }),
    });
}

function confirmDelete() {
    confirm.require({
        message: t('stampCard.confirmDelete'),
        header: t('common.delete'),
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.stamp-cards.destroy', props.stampCard.id), {
                onSuccess: () => router.visit(route('admin.stamp-cards.index')),
            });
        },
    });
}
</script>

<template>
    <Head :title="`${t('stampCard.title')} — ${stampCard.name}`" />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Button icon="pi pi-arrow-left" severity="secondary" text
                    @click="router.visit(route('admin.stamp-cards.index'))" />
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stampCard.name }}</h1>
                <Tag :value="stampCard.is_active ? t('common.active') : t('common.inactive')"
                    :severity="stampCard.is_active ? 'success' : 'secondary'" />
            </div>
            <Button :label="t('common.delete')" icon="pi pi-trash" severity="danger"
                @click="confirmDelete" />
        </div>

        <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-5 max-w-2xl">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ t('delivery.restaurant') }}</label>
                    <Select v-model="form.restaurant_id"
                        :options="restaurants.map(r => ({ label: r.name, value: r.id }))"
                        option-label="label" option-value="value" class="w-full" />
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ t('common.name') }}</label>
                    <InputText v-model="form.name" class="w-full" />
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ t('common.description') }}</label>
                    <Textarea v-model="form.description" rows="2" class="w-full" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('stampCard.stampsRequired') }}</label>
                    <InputNumber v-model="form.stamps_required" :min="1" :max="100" class="w-full" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('stampCard.rewardType') }}</label>
                    <Select v-model="form.reward_type" :options="REWARD_TYPES"
                        option-label="label" option-value="value" class="w-full" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('stampCard.rewardValue') }}</label>
                    <InputNumber v-model="form.reward_value" :min="0" :max-fraction-digits="2" class="w-full" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">{{ t('stampCard.rewardDescription') }}</label>
                    <InputText v-model="form.reward_description" class="w-full" />
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
                <Button :label="t('common.save')" icon="pi pi-check" type="submit"
                    :loading="form.processing" />
            </div>
        </form>
    </div>
</template>
