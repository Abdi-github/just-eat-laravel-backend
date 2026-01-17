<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast   = useToast();
const confirm = useConfirm();

const props = defineProps<{
    stampCards: {
        data: Array<{
            id: number;
            name: string;
            stamps_required: number;
            reward_type: string;
            reward_value: number;
            reward_description: string;
            is_active: boolean;
            valid_from?: string;
            valid_until?: string;
            restaurant?: { id: number; name: string };
        }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    restaurants: Array<{ id: number; name: string }>;
    filters: Record<string, string>;
}>();

const localFilters = ref({ search: '', restaurant_id: '', active: '' });

function applyFilters() {
    router.get(route('admin.stamp-cards.index'), localFilters.value, { preserveState: true, replace: true });
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(() => localFilters.value.search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});
watch(() => [localFilters.value.restaurant_id, localFilters.value.active], () => applyFilters());

function confirmDelete(id: number) {
    confirm.require({
        message: t('stampCard.confirmDelete'),
        header: t('common.delete'),
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('admin.stamp-cards.destroy', id), {
                onSuccess: () => toast.add({ severity: 'success', summary: t('common.deleted'), life: 2000 }),
            });
        },
    });
}
</script>

<template>
    <Head :title="t('stampCard.title')" />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('stampCard.title') }}</h1>
            <Button :label="t('stampCard.create')" icon="pi pi-plus"
                @click="router.visit(route('admin.stamp-cards.create'))" />
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <InputText v-model="localFilters.search" :placeholder="t('common.search')" />
            <Select v-model="localFilters.restaurant_id"
                :options="[{ label: t('common.all'), value: '' }, ...restaurants.map(r => ({ label: r.name, value: String(r.id) }))]"
                option-label="label" option-value="value" :placeholder="t('delivery.restaurant')" />
            <Select v-model="localFilters.active"
                :options="[{ label: t('common.all'), value: '' }, { label: t('common.active'), value: '1' }, { label: t('common.inactive'), value: '0' }]"
                option-label="label" option-value="value" :placeholder="t('common.status')" />
            <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
            <DataTable :value="stampCards.data" class="text-sm">
                <Column field="id" header="ID" style="width: 60px" />
                <Column :header="t('common.name')">
                    <template #body="{ data }">{{ data.name }}</template>
                </Column>
                <Column :header="t('delivery.restaurant')">
                    <template #body="{ data }">{{ data.restaurant?.name ?? '—' }}</template>
                </Column>
                <Column :header="t('stampCard.stampsRequired')">
                    <template #body="{ data }">{{ data.stamps_required }}</template>
                </Column>
                <Column :header="t('stampCard.reward')">
                    <template #body="{ data }">
                        {{ data.reward_type === 'PERCENTAGE' ? `${data.reward_value}%` : `CHF ${data.reward_value}` }}
                        — {{ data.reward_description }}
                    </template>
                </Column>
                <Column :header="t('common.status')">
                    <template #body="{ data }">
                        <Tag :value="data.is_active ? t('common.active') : t('common.inactive')"
                            :severity="data.is_active ? 'success' : 'secondary'" />
                    </template>
                </Column>
                <Column :header="t('common.actions')" style="width: 100px">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button icon="pi pi-eye" severity="secondary" text size="small"
                                @click="router.visit(route('admin.stamp-cards.show', data.id))" />
                            <Button icon="pi pi-pencil" severity="info" text size="small"
                                @click="router.visit(route('admin.stamp-cards.show', data.id))" />
                            <Button icon="pi pi-trash" severity="danger" text size="small"
                                @click="confirmDelete(data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
