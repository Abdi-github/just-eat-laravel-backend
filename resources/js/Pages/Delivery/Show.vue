<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import Button from 'primevue/button';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const toast = useToast();

const props = defineProps<{
    delivery: {
        id: number;
        status: string;
        order?: {
            id: number;
            order_number: string;
            total: number;
            special_instructions?: string;
        };
        restaurant?: { id: number; name: string; phone?: string };
        courier?: { id: number; first_name: string; last_name: string; email: string; phone?: string };
        pickup_address?:   string;
        delivery_address?: Record<string, string>;
        delivery_fee?:     number;
        distance_km?:      number;
        estimated_pickup_at?:   string;
        estimated_delivery_at?: string;
        assigned_at?:   string;
        picked_up_at?:  string;
        in_transit_at?: string;
        delivered_at?:  string;
        cancelled_at?:  string;
        cancellation_reason?: string;
        notes?: string;
        created_at: string;
    };
    couriers: Array<{ id: number; first_name: string; last_name: string; email: string }>;
}>();

const STATUSES = ['PENDING', 'ASSIGNED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED', 'FAILED'];

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

function fmt(dt?: string) {
    if (!dt) return '—';
    return new Date(dt).toLocaleString();
}

// ── Assign Courier Dialog ──────────────────────────────────────────────────
const showAssignDialog  = ref(false);
const selectedCourierId = ref<number | null>(null);

function submitAssign() {
    if (!selectedCourierId.value) return;
    router.patch(route('admin.deliveries.assign', props.delivery.id), { courier_id: selectedCourierId.value }, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: t('delivery.courierAssigned'), life: 2000 });
            showAssignDialog.value = false;
        },
    });
}

// ── Update Status Dialog ───────────────────────────────────────────────────
const showStatusDialog = ref(false);
const newStatus        = ref('');
const cancelReason     = ref('');

function submitStatus() {
    if (!newStatus.value) return;
    router.patch(route('admin.deliveries.status', props.delivery.id), {
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
    <Head :title="`${t('delivery.title')} #${delivery.id}`" />

    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Button icon="pi pi-arrow-left" severity="secondary" text @click="router.visit(route('admin.deliveries.index'))" />
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('delivery.title') }} #{{ delivery.id }}
                </h1>
                <Tag :value="delivery.status" :severity="statusSeverity(delivery.status)" />
            </div>
            <div class="flex gap-2">
                <Button :label="t('delivery.assignCourier')" icon="pi pi-user-plus" severity="info"
                    v-if="['PENDING','ASSIGNED'].includes(delivery.status)"
                    @click="showAssignDialog = true" />
                <Button :label="t('delivery.updateStatus')" icon="pi pi-refresh" severity="warning"
                    v-if="!['DELIVERED','CANCELLED','FAILED'].includes(delivery.status)"
                    @click="showStatusDialog = true" />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Order Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h2 class="text-lg font-semibold">{{ t('delivery.orderInfo') }}</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.orderNumber') }}</span>
                        <span class="font-medium">{{ delivery.order?.order_number ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.total') }}</span>
                        <span class="font-medium">CHF {{ delivery.order?.total?.toFixed(2) ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.deliveryFee') }}</span>
                        <span>CHF {{ delivery.delivery_fee?.toFixed(2) ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.distance') }}</span>
                        <span>{{ delivery.distance_km ?? '—' }} km</span>
                    </div>
                    <div v-if="delivery.order?.special_instructions" class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.instructions') }}</span>
                        <span class="text-right max-w-xs">{{ delivery.order.special_instructions }}</span>
                    </div>
                </div>
            </div>

            <!-- Restaurant & Courier -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h2 class="text-lg font-semibold">{{ t('delivery.participants') }}</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.restaurant') }}</span>
                        <span>{{ delivery.restaurant?.name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.courier') }}</span>
                        <span v-if="delivery.courier">
                            {{ delivery.courier.first_name }} {{ delivery.courier.last_name }}
                        </span>
                        <Tag v-else severity="warning" :value="t('delivery.noCourier')" />
                    </div>
                    <div v-if="delivery.courier" class="flex justify-between">
                        <span class="text-gray-500">{{ t('common.email') }}</span>
                        <span>{{ delivery.courier.email }}</span>
                    </div>
                </div>
            </div>

            <!-- Addresses -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h2 class="text-lg font-semibold">{{ t('delivery.addresses') }}</h2>
                <div class="text-sm space-y-2">
                    <div>
                        <span class="text-gray-500 block">{{ t('delivery.pickupAddress') }}</span>
                        <span>{{ delivery.pickup_address ?? '—' }}</span>
                    </div>
                    <div v-if="delivery.delivery_address">
                        <span class="text-gray-500 block">{{ t('delivery.deliveryAddress') }}</span>
                        <span>{{ delivery.delivery_address.street }}, {{ delivery.delivery_address.city }}</span>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h2 class="text-lg font-semibold">{{ t('delivery.timeline') }}</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.created') }}</span>
                        <span>{{ fmt(delivery.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.assigned') }}</span>
                        <span>{{ fmt(delivery.assigned_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.pickedUp') }}</span>
                        <span>{{ fmt(delivery.picked_up_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.inTransit') }}</span>
                        <span>{{ fmt(delivery.in_transit_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.delivered') }}</span>
                        <span>{{ fmt(delivery.delivered_at) }}</span>
                    </div>
                    <div v-if="delivery.cancelled_at" class="flex justify-between">
                        <span class="text-gray-500">{{ t('delivery.cancelled') }}</span>
                        <span>{{ fmt(delivery.cancelled_at) }}</span>
                    </div>
                    <div v-if="delivery.cancellation_reason">
                        <span class="text-gray-500 block">{{ t('delivery.cancellationReason') }}</span>
                        <span class="text-red-500">{{ delivery.cancellation_reason }}</span>
                    </div>
                </div>
            </div>
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
