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
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();

const props = defineProps<{
    deliveries: {
        data: Array<{
            id: number;
            status: string;
            order?: { id: number; order_number: string; total: number };
            restaurant?: { id: number; name: string };
            courier?: { id: number; first_name: string; last_name: string };
            created_at: string;
        }>;
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
    };
    couriers: Array<{ id: number; first_name: string; last_name: string; email: string }>;
    filters: Record<string, string>;
}>();

const STATUSES = ['PENDING', 'ASSIGNED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED', 'FAILED'];

const localFilters = ref({ status: '', search: '' });

function applyFilters() {
    router.get(route('admin.deliveries.index'), localFilters.value, { preserveState: true, replace: true });
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(() => localFilters.value.search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});
watch(() => localFilters.value.status, () => applyFilters());

function statusSeverity(status: string) {
    const map: Record<string, string> = {
        PENDING:    'warning',
        ASSIGNED:   'info',
        PICKED_UP:  'info',
        IN_TRANSIT: 'primary',
        DELIVERED:  'success',
        CANCELLED:  'secondary',
        FAILED:     'danger',
    };
    return map[status] ?? 'secondary';
}

// ── Assign Courier Dialog ───────────────────────────────────────────────────
const showAssignDialog  = ref(false);
const assigningId       = ref<number | null>(null);
const selectedCourierId = ref<number | null>(null);

function openAssign(deliveryId: number) {
    assigningId.value       = deliveryId;
    selectedCourierId.value = null;
    showAssignDialog.value  = true;
}

function submitAssign() {
    if (!assigningId.value || !selectedCourierId.value) return;
    router.patch(route('admin.deliveries.assign', assigningId.value), { courier_id: selectedCourierId.value }, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: t('delivery.courierAssigned'), life: 2000 });
            showAssignDialog.value = false;
        },
    });
}

// ── Update Status Dialog ───────────────────────────────────────────────────
const showStatusDialog  = ref(false);
const statusDeliveryId  = ref<number | null>(null);
const newStatus         = ref('');
const cancelReason      = ref('');

function openStatusUpdate(deliveryId: number) {
    statusDeliveryId.value = deliveryId;
    newStatus.value        = '';
    cancelReason.value     = '';
    showStatusDialog.value = true;
}

function submitStatus() {
    if (!statusDeliveryId.value || !newStatus.value) return;
    router.patch(route('admin.deliveries.status', statusDeliveryId.value), {
        status: newStatus.value,
        reason: cancelReason.value || undefined,
    }, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: t('delivery.statusUpdated'), life: 2000 });
            showStatusDialog.value = false;
        },
    });
}
</script>

<template>
    <Head :title="t('delivery.title')" />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('delivery.title') }}</h1>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <InputText v-model="localFilters.search" :placeholder="t('common.search')" />
            <Select v-model="localFilters.status" :options="[{ label: t('common.all'), value: '' }, ...STATUSES.map(s => ({ label: s, value: s }))]"
                option-label="label" option-value="value" :placeholder="t('common.status')" />
            <Button :label="t('common.filter')" icon="pi pi-filter" @click="applyFilters" />
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
            <DataTable :value="deliveries.data" class="text-sm">
                <Column field="id" header="ID" style="width: 60px" />
                <Column :header="t('delivery.order')">
                    <template #body="{ data }">
                        <span v-if="data.order">{{ data.order.order_number }}</span>
                    </template>
                </Column>
                <Column :header="t('delivery.restaurant')">
                    <template #body="{ data }">{{ data.restaurant?.name }}</template>
                </Column>
                <Column :header="t('delivery.courier')">
                    <template #body="{ data }">
                        <span v-if="data.courier">{{ data.courier.first_name }} {{ data.courier.last_name }}</span>
                        <span v-else class="text-gray-400">—</span>
                    </template>
                </Column>
                <Column :header="t('common.status')">
                    <template #body="{ data }">
                        <Tag :value="data.status" :severity="statusSeverity(data.status)" />
                    </template>
                </Column>
                <Column :header="t('common.createdAt')">
                    <template #body="{ data }">{{ new Date(data.created_at).toLocaleDateString() }}</template>
                </Column>
                <Column :header="t('common.actions')" style="width: 140px">
                    <template #body="{ data }">
                        <div class="flex gap-1">
                            <Button icon="pi pi-eye" severity="secondary" text size="small"
                                @click="router.visit(route('admin.deliveries.show', data.id))" />
                            <Button v-if="data.status === 'PENDING'" icon="pi pi-user-plus"
                                severity="info" text size="small" :title="t('delivery.assignCourier')"
                                @click="openAssign(data.id)" />
                            <Button icon="pi pi-refresh" severity="warning" text size="small"
                                :title="t('delivery.updateStatus')" @click="openStatusUpdate(data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <!-- Assign Courier Dialog -->
    <Dialog v-model:visible="showAssignDialog" :header="t('delivery.assignCourier')" modal :style="{ width: '420px' }">
        <div class="space-y-3 pt-2">
            <Select v-model="selectedCourierId"
                :options="couriers.map(c => ({ label: `${c.first_name} ${c.last_name} (${c.email})`, value: c.id }))"
                option-label="label" option-value="value" :placeholder="t('delivery.selectCourier')" class="w-full" />
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" severity="secondary" @click="showAssignDialog = false" />
            <Button :label="t('delivery.assign')" icon="pi pi-check" @click="submitAssign" :disabled="!selectedCourierId" />
        </template>
    </Dialog>

    <!-- Update Status Dialog -->
    <Dialog v-model:visible="showStatusDialog" :header="t('delivery.updateStatus')" modal :style="{ width: '420px' }">
        <div class="space-y-3 pt-2">
            <Select v-model="newStatus"
                :options="STATUSES.map(s => ({ label: s, value: s }))"
                option-label="label" option-value="value" :placeholder="t('common.status')" class="w-full" />
            <div v-if="newStatus === 'CANCELLED'">
                <label class="block text-sm font-medium mb-1">{{ t('delivery.cancellationReason') }}</label>
                <Textarea v-model="cancelReason" rows="3" class="w-full" />
            </div>
        </div>
        <template #footer>
            <Button :label="t('common.cancel')" severity="secondary" @click="showStatusDialog = false" />
            <Button :label="t('common.save')" icon="pi pi-check" @click="submitStatus" :disabled="!newStatus" />
        </template>
    </Dialog>
</template>
