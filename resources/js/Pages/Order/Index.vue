<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import { format } from 'date-fns';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();

const props = defineProps<{
    orders: {
        data: Array<{
            id: number;
            order_number: string;
            status: string;
            total: number;
            order_type: string;
            created_at: string;
            user: { first_name: string; last_name: string } | null;
            restaurant: { name: string } | null;
        }>;
        meta: { current_page: number; last_page: number; total: number; per_page: number };
        links: { next: string | null; prev: string | null };
    };
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');

const statusOptions = [
    { label: t('common.all'), value: '' },
    { label: t('order.statuses.pending'), value: 'pending' },
    { label: t('order.statuses.confirmed'), value: 'confirmed' },
    { label: t('order.statuses.preparing'), value: 'preparing' },
    { label: t('order.statuses.picked_up'), value: 'picked_up' },
    { label: t('order.statuses.delivered'), value: 'delivered' },
    { label: t('order.statuses.cancelled'), value: 'cancelled' },
];

const statusSeverity: Record<string, string> = {
    pending: 'warn',
    confirmed: 'info',
    preparing: 'info',
    picked_up: 'secondary',
    delivered: 'success',
    cancelled: 'danger',
};

function applyFilters() {
    router.get(route('admin.orders.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveScroll: true, preserveState: true });
}

function resetFilters() {
    search.value = '';
    statusFilter.value = '';
    applyFilters();
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});
watch(statusFilter, () => applyFilters());

function updateStatus(id: number, status: string) {
    router.put(route('admin.orders.update', id), { status }, {
        preserveScroll: true,
        onSuccess: () => toast.add({ severity: 'success', summary: 'Status updated', life: 2000 }),
    });
}
</script>

<template>
    <Head :title="t('order.title')" />

    <div class="space-y-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('order.title') }}</h1>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500">{{ t('common.search') }}</label>
                <InputText v-model="search" :placeholder="t('order.orderNumber')" />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500">{{ t('common.status') }}</label>
                <Select v-model="statusFilter" :options="statusOptions" option-label="label" option-value="value" />
            </div>
            <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
            <Button :label="t('common.reset')" icon="pi pi-times" severity="secondary" @click="resetFilters" />
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <DataTable :value="orders.data" striped-rows sort-mode="single">
                <Column field="order_number" :header="t('order.orderNumber')" sortable />
                <Column :header="t('order.customer')">
                    <template #body="{ data }">
                        {{ data.user ? `${data.user.first_name} ${data.user.last_name}` : '-' }}
                    </template>
                </Column>
                <Column :header="t('order.restaurant')">
                    <template #body="{ data }">
                        {{ data.restaurant?.name ?? '-' }}
                    </template>
                </Column>
                <Column :header="t('order.status')">
                    <template #body="{ data }">
                        <Tag
                            :value="t(`order.statuses.${data.status}`)"
                            :severity="statusSeverity[data.status] ?? 'secondary'"
                        />
                    </template>
                </Column>
                <Column :header="t('order.total')">
                    <template #body="{ data }">
                        CHF {{ Number(data.total).toFixed(2) }}
                    </template>
                </Column>
                <Column :header="t('common.createdAt')">
                    <template #body="{ data }">
                        {{ format(new Date(data.created_at), 'dd.MM.yyyy HH:mm') }}
                    </template>
                </Column>
                <Column :header="t('common.actions')">
                    <template #body="{ data }">
                        <Link :href="route('admin.orders.show', data.id)">
                            <Button icon="pi pi-eye" text rounded severity="info" size="small" />
                        </Link>
                    </template>
                </Column>
            </DataTable>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 flex justify-between">
                <span>{{ t('common.total') }}: {{ orders.meta?.total ?? orders.data.length }}</span>
                <div class="flex gap-2">
                    <Link v-if="orders.links?.prev" :href="orders.links.prev" preserve-scroll>
                        <Button icon="pi pi-chevron-left" text size="small" />
                    </Link>
                    <span>{{ t('common.page') }} {{ orders.meta?.current_page }} {{ t('common.of') }} {{ orders.meta?.last_page }}</span>
                    <Link v-if="orders.links?.next" :href="orders.links.next" preserve-scroll>
                        <Button icon="pi pi-chevron-right" text size="small" />
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
