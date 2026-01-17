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

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
    transactions: {
        data: Array<{
            id: number;
            amount: number;
            currency: string;
            status: string;
            payment_method: string;
            provider_name: string;
            created_at: string;
            order?: { id: number; order_number: string };
            user?:  { id: number; first_name: string; last_name: string };
        }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    filters: Record<string, string>;
}>();

const STATUSES = [
    'PENDING','PROCESSING','COMPLETED','FAILED','REFUNDED','PARTIAL_REFUND','CANCELLED','EXPIRED',
];
const METHODS = ['credit_card','debit_card','paypal','twint','cash'];

const localFilters = ref({ status: '', method: '', search: '' });

function applyFilters() {
    router.get(route('admin.payments.index'), localFilters.value, { preserveState: true, replace: true });
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(() => localFilters.value.search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});
watch(() => [localFilters.value.status, localFilters.value.method], () => applyFilters());

function statusSeverity(status: string) {
    const map: Record<string, string> = {
        PENDING:        'warning',
        PROCESSING:     'info',
        COMPLETED:      'success',
        FAILED:         'danger',
        REFUNDED:       'secondary',
        PARTIAL_REFUND: 'warn',
        CANCELLED:      'secondary',
        EXPIRED:        'danger',
    };
    return map[status] ?? 'secondary';
}
</script>

<template>
    <Head :title="t('payment.title')" />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('payment.title') }}</h1>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <InputText v-model="localFilters.search" :placeholder="t('common.search')" />
            <Select v-model="localFilters.status"
                :options="[{ label: t('common.all'), value: '' }, ...STATUSES.map(s => ({ label: s, value: s }))]"
                option-label="label" option-value="value" :placeholder="t('common.status')" />
            <Select v-model="localFilters.method"
                :options="[{ label: t('common.all'), value: '' }, ...METHODS.map(m => ({ label: m, value: m }))]"
                option-label="label" option-value="value" :placeholder="t('payment.method')" />
            <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
            <DataTable :value="transactions.data" class="text-sm" row-hover
                @row-click="(e) => router.visit(route('admin.payments.show', e.data.id))">
                <Column field="id" header="ID" style="width: 60px" />
                <Column :header="t('delivery.orderNumber')">
                    <template #body="{ data }">{{ data.order?.order_number ?? '—' }}</template>
                </Column>
                <Column :header="t('common.user')">
                    <template #body="{ data }">
                        <span v-if="data.user">{{ data.user.first_name }} {{ data.user.last_name }}</span>
                    </template>
                </Column>
                <Column :header="t('payment.amount')">
                    <template #body="{ data }">{{ data.currency }} {{ data.amount?.toFixed(2) }}</template>
                </Column>
                <Column :header="t('payment.method')">
                    <template #body="{ data }">{{ data.payment_method }}</template>
                </Column>
                <Column :header="t('payment.provider')">
                    <template #body="{ data }">{{ data.provider_name }}</template>
                </Column>
                <Column :header="t('common.status')">
                    <template #body="{ data }">
                        <Tag :value="data.status" :severity="statusSeverity(data.status)" />
                    </template>
                </Column>
                <Column :header="t('common.createdAt')">
                    <template #body="{ data }">{{ new Date(data.created_at).toLocaleDateString() }}</template>
                </Column>
                <Column :header="t('common.actions')" style="width: 60px">
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" severity="secondary" text size="small"
                            @click.stop="router.visit(route('admin.payments.show', data.id))" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
